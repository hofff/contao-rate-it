<?php

/**
 * This file is part of hofff/contao-rate-it.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author     David Molineus <david@hofff.com>
 * @copyright  2019-2020 hofff.com.
 * @license    https://github.com/hofff/contao-rate-it/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace Hofff\Contao\RateIt\Console\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Hofff\Contao\RateIt\Rating\RatingTypes;
use PDO;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function time;

final class MigrateCommand extends Command
{
    /** @var string */
    protected static $defaultName = 'hofff-rate-it:migrate';

    /** @var Connection */
    private $connection;

    /** @var RatingTypes */
    private $ratingTypes;

    public function __construct(Connection $connection, RatingTypes $ratingTypes)
    {
        parent::__construct();

        $this->connection  = $connection;
        $this->ratingTypes = $ratingTypes;
    }

    protected function configure() : void
    {
        $this->addArgument(
            'task',
            InputArgument::OPTIONAL,
            'Decide which migration task should be run',
            'article-to-page'
        );

        $this->addOption(
            'position',
            'p',
            InputOption::VALUE_REQUIRED,
            'Position of the rating being added to a page',
            'before'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $task = $input->getArgument('task');

        switch ($task) {
            case 'article-to-page':
                return $this->migrateArticlesToPages($input);
                break;

            default:
                throw new \InvalidArgumentException(sprintf('Task "%s" is not supported.', $task));
        }
    }

    private function migrateArticlesToPages(InputInterface $input) : int
    {
        $unratedPagesWithArticleRatings = $this->getUnratedPagesWithArticleRatings();
        $createdRatings = [];

        while ($row = $unratedPagesWithArticleRatings->fetch(PDO::FETCH_ASSOC)) {
            $this->createRateItItem($row['pageId'], $input->getOption('position'));
        }

        $this->migrateArticleRatings();

        return 0;
    }

    private function getUnratedPagesWithArticleRatings() : Statement
    {
        $sql = <<<'SQL'
SELECT 
       p.`id` AS 'pageId'
FROM `tl_page` p
INNER JOIN `tl_article` a               ON a.`pid` = p.`id`
LEFT JOIN `tl_rateit_items` ri_page     ON p.`id` = ri_page.`rkey`    AND ri_page.`typ` = 'page'
INNER JOIN `tl_rateit_items` ri_article ON a.`id` = ri_article.`rkey` AND ri_article.`typ` = 'article'
WHERE 
    ri_page.`id` IS NULL
    AND ri_article.`id` IS NOT NULL
GROUP BY p.id
SQL;

        return $this->connection->executeQuery($sql);
    }

    private function createRateItItem($pageId, string $position) : void
    {
        $sourceInformation = $this->ratingTypes->sourceInformation('page', (int) $pageId);
        if (!$sourceInformation) {
            return;
        }

        $arrSet = [
            'rkey'         => $pageId,
            'tstamp'       => time(),
            'typ'          => 'page',
            'createdat'    => time(),
            'title'        => $sourceInformation->title(),
            'active'       => '1',
            'parentstatus' => $sourceInformation->parentStatus(),
        ];

        $this->connection->insert('tl_rateit_items', $arrSet);
        $this->connection->update('tl_page', ['addRating' => '1', 'rateit_position' => $position], ['id' => $pageId]);
    }

    private function migrateArticleRatings() : void
    {
        $sql = <<<'SQL'

UPDATE
    tl_rateit_ratings r
INNER JOIN tl_rateit_items ri_article ON ri_article.id=r.pid AND ri_article.typ='article'
INNER JOIN tl_article a on a.id=ri_article.rkey
INNER JOIN tl_rateit_items ri_page ON ri_page.rkey = a.pid AND ri_page.typ='page'
SET r.pid=ri_page.id;
SQL;

        $this->connection->executeQuery($sql);
        $this->connection->executeQuery('UPDATE tl_article SET addRating=\'\'');
        $this->connection->delete('tl_rateit_items', ['typ' => 'article']);
    }
}
