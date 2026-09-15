<?php

declare(strict_types=1);

namespace Hofff\Contao\RateIt\Rating;

use Contao\Config;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Date;
use Doctrine\DBAL\Connection;
use stdClass;

use function round;
use function sprintf;
use function str_replace;

/**
 * Data access for tl_rateit_items / tl_rateit_ratings, shared by the backend list and view controllers.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class RatingItemRepository
{
    private const int DEFAULT_PERPAGE = 10;

    public function __construct(
        private readonly Connection $connection,
        private readonly ContaoFramework $framework,
        private readonly RatingTypes $ratingTypes,
    ) {
    }

    public function perPageSize(): int
    {
        $perpage = (int) $this->config()->get('rating_listsize');

        return $perpage > 0 ? $perpage : self::DEFAULT_PERPAGE;
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return list<stdClass>
     *
     * @psalm-suppress MoreSpecificReturnType, LessSpecificReturnStatement Psalm's inferred shape only
     *     covers the keys assigned in this method and omits the raw SQL columns (rkey, title, typ, ...)
     *     that callers and templates access dynamically on the returned stdClass instances.
     */
    public function findRatingItems(array $options, bool $noLimit = false): array
    {
        $sql = 'SELECT i.id as item_id,
					i.rkey AS rkey,
					i.title as title,
					i.typ as typ,
					i.createdat as createdat,
					i.active as active,
                    i.parentstatus as parentstatus,
					IFNULL(AVG(r.rating),0) AS rating,
					COUNT( r.rating ) AS totalRatings
					FROM tl_rateit_items i
					LEFT OUTER JOIN tl_rateit_ratings r
					ON (i.id = r.pid)
					%w
					GROUP BY rkey, title, item_id, typ, createdat, active, parentstatus
					%o
					%l';

        $cntSql = 'SELECT COUNT(*) FROM tl_rateit_items i %s';

        $where      = '';
        $firstWhere = true;
        $limit      = '';
        $order      = '';
        $params     = [];

        foreach ($options as $k => $v) {
            if ($k === 'find') {
                if (! $firstWhere) {
                    $where .= ' AND';
                }

                $where     .= ' title like ?';
                $params[]   = '%' . $v . '%';
                $firstWhere = false;
            } elseif ($k !== 'order' && $k !== 'limit' && $k !== 'first') {
                if (! $firstWhere) {
                    $where .= ' AND';
                }

                $where     .= sprintf(' %s=?', $k);
                $params[]   = $v;
                $firstWhere = false;
            } else {
                if ($k === 'limit' && ! $noLimit) {
                    $cntRows = $v;
                } elseif ($k === 'first' && ! $noLimit) {
                    $first = $v;
                }
            }
        }

        if (isset($cntRows) && isset($first)) {
            $limit = sprintf('LIMIT %d, %d', $first, $cntRows);
        }

        if ($where !== '') {
            $where = 'WHERE ' . $where;
        }

        if (isset($options['order']) && $options['order'] !== '') {
            $order = 'ORDER BY ' . $options['order'];
        }

        $sql = str_replace('%o', $order, $sql);
        $sql = str_replace('%w', $where, $sql);
        $sql = str_replace('%l', $limit, $sql);

        $cntSql = str_replace('%s', $where, $cntSql);
        $count  = (int) $this->connection->fetchOne($cntSql, $params);

        $arrRatingItems = $this->connection->fetchAllAssociative($sql, $params);
        $arrReturn      = [];
        foreach ($arrRatingItems as $rating) {
            if ($rating['active'] !== '1') {
                $rating['active'] = '0';
            }

            $rating['percent']            = $rating['rating'];
            $rating['rating']             = $this->percentToStars((float) $rating['percent']);
            $rating['stars']              = $this->stars();
            $rating['totcount']           = $count;
            $rating['createdatFormatted'] = $this->date()->parse(
                (string) $this->config()->get('dateFormat'),
                (int) $rating['createdat'],
            );
            $arrReturn[]                  = (object) $rating;
        }

        return $arrReturn;
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return list<stdClass>
     *
     * @psalm-suppress MoreSpecificReturnType, LessSpecificReturnStatement Psalm's inferred shape only
     *     covers the keys assigned in this method and omits the raw SQL columns (rkey, title, typ, ...)
     *     that callers and templates access dynamically on the returned stdClass instances.
     */
    public function findRatings(stdClass $ext, array $options = []): array
    {
        $count = (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM tl_rateit_ratings r WHERE r.pid=?',
            [$ext->item_id],
        );

        foreach ($options as $k => $v) {
            if ($k === 'limit') {
                $cntRows = $v;
            } elseif ($k === 'first') {
                $first = $v;
            }
        }

        $sql = 'SELECT id AS rating_id, session_id, memberid, rating, createdat
            FROM tl_rateit_ratings r
            WHERE r.pid=?
            ORDER BY createdat DESC';

        if (isset($cntRows) && isset($first)) {
            $sql .= sprintf("\n LIMIT %d, %d", $first, $cntRows);
        }

        $arrRatings = $this->connection->fetchAllAssociative($sql, [$ext->item_id]);
        $arrReturn  = [];
        foreach ($arrRatings as $rating) {
            $rating['percent']            = $rating['rating'];
            $rating['rating']             = $this->percentToStars((float) $rating['percent']);
            $rating['stars']              = $this->stars();
            $rating['totcount']           = $count;
            $rating['createdatFormatted'] = $this->date()->parse(
                (string) $this->config()->get('datimFormat'),
                (int) $rating['createdat'],
            );
            if ($rating['memberid'] !== null) {
                $member           = $this->connection->fetchAssociative(
                    'SELECT firstname, lastname FROM tl_member WHERE id=?',
                    [$rating['memberid']],
                );
                $rating['member'] = $member
                    ? ($member['firstname'] . ' ' . $member['lastname'])
                    : 'ID ' . $rating['memberid'];
            }

            $arrReturn[] = (object) $rating;
        }

        return $arrReturn;
    }

    /**
     * @return array<array-key, stdClass>
     *
     * @psalm-suppress MoreSpecificReturnType, LessSpecificReturnStatement Psalm's inferred shape only
     *     covers the keys assigned in this method and omits the raw SQL columns that callers and
     *     templates access dynamically on the returned stdClass instances.
     */
    public function getRatingStatistics(int $itemId): array
    {
        $arrRatingStatistics = $this->connection->fetchAllAssociative(
            'SELECT rating, count(*) as count
				FROM tl_rateit_ratings r
				WHERE r.pid=?
				GROUP BY rating
				ORDER BY rating',
            [$itemId],
        );

        $arrReturn = [];
        foreach ($arrRatingStatistics as $rating) {
            $rating['percent']             = $rating['rating'];
            $rating['rating']              = $this->percentToStars((float) $rating['percent']);
            $arrReturn[$rating['percent']] = (object) $rating;
        }

        return $arrReturn;
    }

    /** @return list<string> */
    public function getUsedTypes(): array
    {
        return $this->connection->fetchFirstColumn('SELECT typ FROM tl_rateit_items GROUP BY typ ORDER BY typ');
    }

    /** @return list<array<string, mixed>> */
    public function getMonthsChartRows(int $itemId): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT
                count(*) AS anzahl,
                avg(rating) AS bewertung,
                month(date(FROM_UNIXTIME(createdat))) AS monat,
                year(date(FROM_UNIXTIME(createdat))) AS jahr
                FROM tl_rateit_ratings r
                WHERE r.pid=?
                GROUP BY monat, jahr
                ORDER BY jahr DESC , monat DESC
                LIMIT 0 , 12',
            [$itemId],
        );
    }

    /** @return list<string> */
    public function buildPagesList(int $totrecs, int $perpage): array
    {
        $pages = [];
        if ($perpage > 0) {
            $first = 1;
            while ($totrecs > 0) {
                $cnt      = $totrecs > $perpage ? $perpage : $totrecs;
                $pages[]  = $first . ' - ' . ($first + $cnt - 1);
                $first   += $cnt;
                $totrecs -= $cnt;
            }
        }

        return $pages;
    }

    public function updateParentInformation(): void
    {
        $result = $this->connection->executeQuery('SELECT id, rkey, typ FROM tl_rateit_items');

        while (($row = $result->fetchAssociative()) !== false) {
            $information = $this->ratingTypes->sourceInformation((string) $row['typ'], (int) $row['rkey']);

            if ($information === null) {
                $this->connection->update('tl_rateit_items', ['parentstatus' => 'r'], ['id' => $row['id']]);

                continue;
            }

            $this->connection->update(
                'tl_rateit_items',
                ['parentstatus' => $information->parentStatus(), 'title' => $information->title()],
                ['id' => $row['id']],
            );
        }
    }

    public function clearRatings(string $rkey, string $typ, bool $removeParent): void
    {
        $this->connection->beginTransaction();

        $itemId = $this->connection->fetchOne(
            'SELECT id FROM tl_rateit_items WHERE rkey = ? AND typ = ?',
            [$rkey, $typ],
        );

        if ($itemId === false) {
            $this->connection->commit();

            return;
        }

        $this->connection->executeStatement('DELETE FROM tl_rateit_ratings WHERE pid = ?', [$itemId]);

        if ($removeParent) {
            $this->connection->executeStatement('DELETE FROM tl_rateit_items WHERE id = ?', [$itemId]);
        }

        $this->connection->commit();
    }

    public function stars(): int
    {
        $stars = (int) $this->config()->get('rating_count');

        return $stars > 0 ? $stars : 5;
    }

    private function percentToStars(float $percent): float
    {
        $modifier = (float) (100 / $this->stars());

        return round($percent / $modifier, 1);
    }

    /** @return Adapter<Config> */
    private function config(): Adapter
    {
        return $this->framework->getAdapter(Config::class);
    }

    /** @return Adapter<Date> */
    private function date(): Adapter
    {
        return $this->framework->getAdapter(Date::class);
    }
}
