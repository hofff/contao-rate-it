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

namespace Hofff\Contao\RateIt\Rating\Comments;

use Contao\Controller;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\System;
use Doctrine\DBAL\Connection;

use function is_array;

final readonly class CommentsTitleGenerator
{
    public function __construct(private Connection $connection, private ContaoFramework $framework)
    {
    }

    public function generate(string $author, string $source, int $sourceId): string
    {
        $this->initialize();
        $title = $this->generateDefaultTitle($author, $source, $sourceId);
        $this->determineSource($source, $sourceId);

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
                if (! isset($GLOBALS['TL_HOOKS']['listComments']) || ! is_array($GLOBALS['TL_HOOKS']['listComments'])) {
                    return $title;
                }

                $statement = $this->connection
                    ->prepare('SELECT * FROM tl_comments WHERE source=? AND parent=? LIMIT  0,1');

                $result = $statement->executeQuery([$source, $sourceId]);
                if ($result->rowCount() === 0) {
                    return $title;
                }

                $row = $result->fetchAssociative();
                foreach ($GLOBALS['TL_HOOKS']['listComments'] as $callback) {
                    $callback[0] = System::importStatic($callback[0]);

                    if ($tmp = $callback[0]->{$callback[1]}($row)) {
                        $title .= $tmp;
                        break;
                    }
                }

                return $title;
        }

        $result = $statement->executeQuery([$sourceId]);
        if ($result->rowCount() === 1) {
            $title .= ' - ' . $result->fetchOne();
        }

        return $title;
    }

    private function initialize(): void
    {
        $this->framework->initialize();

        $adapter = $this->framework->getAdapter(Controller::class);
        $adapter->loadLanguageFile('tl_comments');
        $adapter->loadLanguageFile('default');
    }

    private function generateDefaultTitle(string $author, string $source, int $sourceId): string
    {
        $title = $GLOBALS['TL_LANG']['MSC']['com_by'] . ' ' . $author . ' - ';
        $title .= $GLOBALS['TL_LANG']['tl_comments'][$source] ?? $source;
        $title .= ' ' . ((string) $sourceId);

        return $title;
    }

    private function determineSource(string &$source, int &$sourceId): void
    {
        if ($source !== 'tl_content') {
            return;
        }

        $statement = $this->connection->prepare('SELECT ptable,pid FROM tl_content WHERE id=:id LIMIT 0,1');
        $result    = $statement->executeQuery(['id' => $sourceId]);

        if ($result->rowCount() === 0) {
            return;
        }

        $row      = (array) $result->fetchAssociative();
        $source   = $row['ptable'] ?: 'tl_article';
        $sourceId = (int) $row['pid'];
    }
}
