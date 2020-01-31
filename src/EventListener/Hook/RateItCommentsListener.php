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

namespace Hofff\Contao\RateIt\EventListener\Hook;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Model;
use Contao\System;
use Contao\Template;
use Doctrine\DBAL\Connection;
use Hofff\Contao\RateIt\Rating\RatingService;
use PDO;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use function class_exists;
use function strncmp;
use function substr;
use function time;

final class RateItCommentsListener extends RatingListener
{
    /** @var Connection */
    private $connection;

    public function __construct(
        RatingService $ratingService,
        TokenStorage $tokenStorage,
        ContaoFrameworkInterface $framework,
        Connection $connection
    ) {
        parent::__construct($ratingService, $tokenStorage, $framework);

        $this->connection = $connection;
    }

    private $supportedSources = [
        'tl_news' => 'tl_news_archive',
    ];

    public function onParseTemplate(Template $template) : void
    {
        if (strncmp($template->getName(), 'com_', 4) !== 0) {
            return;
        }

        $configuration = $this->getConfiguration($template->source, $template->parent);
        if (! $configuration || ! $configuration->addCommentsRating) {
            return;
        }

        $template->ratit_template  = $this->getRatingTemplate();
        $template->rateit_position = $configuration->rateit_position_comments;
        $template->rating          = $this->getCommentRating($template);
    }

    private function getCommentRating(Template $template) : ?array
    {
        $commentId = (int) substr($template->id, 1);
        $rating    = $this->getRating('comments', $commentId);
        if ($rating !== null) {
            return $rating;
        }

        $this->connection->insert(
            'tl_rateit_items',
            [
                'rkey'         => $commentId,
                'tstamp'       => time(),
                'typ'          => 'comments',
                'createdat'    => time(),
                'title'        => $GLOBALS['TL_LANG']['MSC']['com_by']
                    . ' '
                    . $template->name
                    . ' - '
                    . $this->generateTitle($template->source, $template->parent),
                'active'       => '1',
                'parentstatus' => 'a',
            ]
        );

        return $this->getRating('comments', $commentId);
    }

    private function getConfiguration(string $source, $parent, bool $checkSupportedSources = true) : ?Model
    {
        if ($checkSupportedSources && ! isset($this->supportedSources[$source])) {
            return null;
        }

        $modelClass = Model::getClassFromTable($source);
        if (! class_exists($modelClass)) {
            return null;
        }

        /** @var Model $modelClass */
        $parentRecord = $modelClass::findByPk($parent);
        if (! $parentRecord) {
            return null;
        }

        if (! $checkSupportedSources || $source === $this->supportedSources[$source]) {
            return $parentRecord;
        }

        if (! isset($GLOBALS['TL_DCA'][$parentRecord::getTable()]['config']['ptable'])) {
            return null;
        }

        return $this->getConfiguration(
            $GLOBALS['TL_DCA'][$parentRecord::getTable()]['config']['ptable'],
            $parentRecord->pid,
            false
        );
    }

    private function generateTitle(string $source, $sourceId) : string
    {
        Controller::loadLanguageFile('tl_comments');

        $title  = $GLOBALS['TL_LANG']['tl_comments'][$source] ?? $source;
        $title .= ' ' . $sourceId;

        if ($source === 'tl_content') {
            $statement = $this->connection->prepare('SELECT ptable,pid FROM tl_content WHERE id=:id LIMIT 0,1');
            $statement->execute(['id' => $sourceId]);

            if ($statement->rowCount() === 0) {
                return $title;
            }

            $source   = $statement->fetchColumn(0) ?: 'tl_article';
            $sourceId = $statement->fetchColumn(1);
        }

        switch ($source) {
            case 'tl_page':
                $statement = $this->connection->prepare('SELECT title FROM tl_page WHERE id=? LIMIT 0,1');

                break;

            case 'tl_news':
                $statement = $this->connection->prepare('SELECT headline FROM tl_news WHERE id=?');

                break;

            case 'tl_faq':
                $statement = $this->connection->prepare('SELECT question FROM tl_faq WHERE id=?');

                break;

            case 'tl_calendar_events':
                $statement = $this->connection->prepare('SELECT title FROM tl_calendar_events WHERE id=?');

                break;

            default:
                // HOOK: support custom modules
                if (!isset($GLOBALS['TL_HOOKS']['listComments']) || !is_array($GLOBALS['TL_HOOKS']['listComments'])) {
                    return $title;
                }

                $statement = $this->connection
                    ->prepare('SELECT * FROM tl_comments WHERE source=? AND parent=? LIMIT  0,1');

                $statement->execute([$source, $sourceId]);
                if ($statement->rowCount() === 0) {
                    return $title;
                }

                $row = $statement->fetch(PDO::FETCH_ASSOC);
                foreach ($GLOBALS['TL_HOOKS']['listComments'] as $callback) {
                    $callback[0] = System::importStatic($callback[0]);

                    if ($tmp = $callback[0]->{$callback[1]}($row)) {
                        $title .= $tmp;
                        break;
                    }
                }

                return $title;
        }

        $statement->execute([$sourceId]);
        if ($statement->rowCount() === 1) {
            $title .= ' - ' . $statement->fetchColumn(0);
        }

        return $title;
    }
}
