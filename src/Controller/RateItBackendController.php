<?php

declare(strict_types=1);

namespace Hofff\Contao\RateIt\Controller;

use Contao\Config;
use Contao\CoreBundle\Controller\AbstractBackendController;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\Date;
use Doctrine\DBAL\Connection;
use Hofff\Contao\RateIt\Rating\RatingTypes;
use stdClass;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

use function array_reverse;
use function count;
use function explode;
use function is_array;
use function is_numeric;
use function json_encode;
use function round;
use function sprintf;
use function str_replace;
use function strstr;
use function trim;

use const JSON_THROW_ON_ERROR;

/**
 * Backend UI for browsing, filtering and resetting ratings.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @psalm-suppress PropertyNotSetInConstructor
 */
#[AsController]
final class RateItBackendController extends AbstractBackendController
{
    private const string MODULE_NAME = 'rateit';

    private const int DEFAULT_PERPAGE = 10;

    public function __construct(
        private readonly Connection $connection,
        private readonly ContaoFramework $framework,
        private readonly TranslatorInterface $translator,
        private readonly RatingTypes $ratingTypes,
    ) {
    }

    #[Route(
        '%contao.backend.route_prefix%/rate-it',
        name: 'hofff_contao_rate_it.backend.list',
        methods: ['GET', 'POST'],
        defaults: ['_scope' => 'backend'],
    )]
    public function list(Request $request): Response
    {
        $this->denyAccessUnlessGranted(ContaoCorePermissions::USER_CAN_ACCESS_MODULE, self::MODULE_NAME);

        $rateit           = new stdClass();
        $rateit->f_action = 'list';
        $rateit->f_page   = 0;

        $sessionBag = $this->getBackendSessionBag();

        if ($request->isMethod('POST') && $request->request->get('rateit_action') === $rateit->f_action) {
            $rateit->f_typ          = trim((string) $request->request->get('rateit_typ', ''));
            $rateit->f_active       = trim((string) $request->request->get('rateit_active', ''));
            $rateit->f_parentstatus = trim((string) $request->request->get('rateit_parentstatus', ''));
            $rateit->f_order        = trim((string) $request->request->get('rateit_order', ''));
            $rateit->f_page         = trim((string) $request->request->get('rateit_page', ''));
            $rateit->f_find         = trim((string) $request->request->get('rateit_find', ''));

            $sessionBag?->set('rateit_settings', [
                'rateit_typ'          => $rateit->f_typ,
                'rateit_parentstatus' => $rateit->f_parentstatus,
                'rateit_order'        => $rateit->f_order,
                'rateit_page'         => $rateit->f_page,
                'rateit_find'         => $rateit->f_find,
            ]);
        } else {
            $stg = $sessionBag?->get('rateit_settings');
            if (is_array($stg)) {
                $rateit->f_typ          = trim((string) ($stg['rateit_typ'] ?? ''));
                $rateit->f_active       = trim((string) ($stg['rateit_active'] ?? ''));
                $rateit->f_parentstatus = trim((string) ($stg['rateit_parentstatus'] ?? ''));
                $rateit->f_order        = trim((string) ($stg['rateit_order'] ?? ''));
                $rateit->f_page         = trim((string) ($stg['rateit_page'] ?? ''));
                $rateit->f_find         = trim((string) ($stg['rateit_find'] ?? ''));
            }
        }

        $rateit->f_typ          ??= '';
        $rateit->f_active       ??= '';
        $rateit->f_parentstatus ??= '';
        $rateit->f_find         ??= '';

        if (($rateit->f_order ?? '') === '') {
            $rateit->f_order = 'rating';
        }

        $perpage = (int) $this->config()->get('rating_listsize');
        if ($perpage <= 0) {
            $perpage = self::DEFAULT_PERPAGE;
        }

        $options = [];
        if ($rateit->f_page >= 0 && $perpage > 0) {
            $options['first'] = (int) $rateit->f_page * $perpage;
            $options['limit'] = $perpage;
        }

        if ($rateit->f_typ !== '') {
            $options['typ'] = $rateit->f_typ;
        }

        if ($rateit->f_active !== '') {
            $options['active'] = $rateit->f_active === '0' ? '' : $rateit->f_active;
        }

        if ($rateit->f_parentstatus !== '') {
            $options['parentstatus'] = $rateit->f_parentstatus;
        }

        if ($rateit->f_find !== '') {
            $options['find'] = $rateit->f_find;
        }

        $options['order'] = match ($rateit->f_order) {
            'title' => 'title',
            'typ' => 'typ',
            'createdat' => 'createdat',
            default => 'rating desc'
        };

        $ratingItems = $this->getRatingItems($options);
        if ($rateit->f_page >= 0 && $perpage > 0 && count($ratingItems) === 0) {
            $rateit->f_page   = 0;
            $options['first'] = 0;
            $ratingItems      = $this->getRatingItems($options);
        }

        $totrecs = 0;
        foreach ($ratingItems as $ext) {
            $ext->viewLink = $this->generateUrl(
                'hofff_contao_rate_it.backend.view',
                ['rkey' => $ext->rkey, 'typ' => $ext->typ],
            );
            $totrecs       = $ext->totcount;
        }

        return $this->render('@Contao/backend/rate_it/list.html.twig', [
            'headline'     => $this->trans('tl_rateit.ratings.0'),
            'title'        => $this->trans('tl_rateit.ratings.0'),
            'action'       => $this->generateUrl('hofff_contao_rate_it.backend.list'),
            'resetAction'  => $this->generateUrl('hofff_contao_rate_it.backend.reset'),
            'rateit'       => $rateit,
            'ratingItems'  => $ratingItems,
            'pages'        => $this->buildPagesList($totrecs, $perpage),
            'types'        => $this->getUsedTypes(),
        ]);
    }

    #[Route(
        '%contao.backend.route_prefix%/rate-it/view',
        name: 'hofff_contao_rate_it.backend.view',
        methods: ['GET', 'POST'],
        defaults: ['_scope' => 'backend'],
    )]
    public function view(Request $request): Response
    {
        $this->denyAccessUnlessGranted(ContaoCorePermissions::USER_CAN_ACCESS_MODULE, self::MODULE_NAME);

        $rkey = $request->query->get('rkey', '');
        $typ  = $request->query->get('typ', '');

        foreach (strstr($rkey, '|') ? explode('|', $rkey) : [$rkey] as $key) {
            if (! is_numeric($key)) {
                return $this->redirectToList();
            }
        }

        $rateit           = new stdClass();
        $rateit->f_action = 'view';
        $rateit->f_page   = 0;

        $sessionBag = $this->getBackendSessionBag();

        if ($request->isMethod('POST') && $request->request->get('rateit_action') === $rateit->f_action) {
            $rateit->f_page = trim((string) $request->request->get('rateit_details_page', ''));
            $sessionBag?->set('rateit_settings', ['rateit_details_page' => $rateit->f_page]);
        } else {
            $stg = $sessionBag?->get('rateit_settings');
            if (is_array($stg)) {
                $rateit->f_page = trim((string) ($stg['rateit_details_page'] ?? ''));
            }
        }

        $rateit->f_page = $rateit->f_page === '' ? 0 : (int) $rateit->f_page;

        $perpage = (int) $this->config()->get('rating_listsize');
        if ($perpage <= 0) {
            $perpage = self::DEFAULT_PERPAGE;
        }

        $options = ['rkey' => $rkey, 'typ' => $typ];
        if ($rateit->f_page >= 0 && $perpage > 0) {
            $options['first'] = $rateit->f_page * $perpage;
            $options['limit'] = $perpage;
        }

        $ratingItems = $this->getRatingItems($options, true);
        if (count($ratingItems) < 1) {
            return $this->redirectToList();
        }

        $ext = $ratingItems[0];

        $ext->ratings = $this->getRatings($ext, $options);
        if ($rateit->f_page >= 0 && $perpage > 0 && count($ext->ratings) === 0) {
            $rateit->f_page   = 0;
            $options['first'] = 0;
            $ext->ratings     = $this->getRatings($ext, $options);
        }

        $totrecs = count($ext->ratings) > 0 ? $ext->ratings[0]->totcount : 0;

        $ext->statistics       = $this->getRatingStatistics((int) $ext->item_id);
        $ext->ratingsChartData = $this->getRatingsChartData($ext->statistics);
        $ext->monthsChartData  = $this->getMonthsChartData((int) $ext->item_id);

        return $this->render('@Contao/backend/rate_it/view.html.twig', [
            'headline'    => $this->trans('tl_rateit.ratings.0'),
            'title'       => $this->trans('tl_rateit.ratings.0'),
            'action'      => $this->generateUrl('hofff_contao_rate_it.backend.view', ['rkey' => $rkey, 'typ' => $typ]),
            'rateit'      => $rateit,
            'rating'      => $ext,
            'pages'       => $this->buildPagesList($totrecs, $perpage),
            'maxRating'   => (int) $this->config()->get('rating_count'),
        ]);
    }

    #[Route(
        '%contao.backend.route_prefix%/rate-it/reset',
        name: 'hofff_contao_rate_it.backend.reset',
        methods: ['POST'],
        defaults: ['_scope' => 'backend'],
    )]
    public function reset(Request $request): Response
    {
        $this->denyAccessUnlessGranted(ContaoCorePermissions::USER_CAN_ACCESS_MODULE, self::MODULE_NAME);

        if ($request->request->get('rateit_action') === 'updateinformation') {
            $this->updateParentInformation();

            return $this->redirectToList();
        }

        $ids = $request->request->all('selectedids');
        if ($ids === []) {
            return $this->redirectToList();
        }

        $removeParent = $request->request->get('rateit_action') === 'removeratings';

        foreach ($ids as $id) {
            [$rkey, $typ] = explode('__', (string) $id);

            $this->connection->beginTransaction();

            $itemId = $this->connection->fetchOne(
                'SELECT id FROM tl_rateit_items WHERE rkey = ? AND typ = ?',
                [$rkey, $typ],
            );

            if ($itemId === false) {
                $this->connection->commit();

                continue;
            }

            $this->connection->executeStatement('DELETE FROM tl_rateit_ratings WHERE pid = ?', [$itemId]);

            if ($removeParent) {
                $this->connection->executeStatement('DELETE FROM tl_rateit_items WHERE id = ?', [$itemId]);
            }

            $this->connection->commit();
        }

        return $this->redirectToList();
    }

    private function updateParentInformation(): void
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

    private function redirectToList(): RedirectResponse
    {
        return $this->redirect($this->generateUrl('hofff_contao_rate_it.backend.list'));
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
    private function getRatingItems(array $options, bool $noLimit = false): array
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
    private function getRatings(stdClass $ext, array $options = []): array
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
    private function getRatingStatistics(int $itemId): array
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

    /** @param array<array-key, stdClass> $statistics */
    private function getRatingsChartData(array $statistics): string
    {
        $arr         = [];
        $arr['cols'] = [];
        $arr['rows'] = [];

        $arr['cols'][] = [
            'id'    => 'rating',
            'label' => $this->trans('tl_rateit.rating_chart_legend.2'),
            'type'  => 'string',
        ];
        $arr['cols'][] = [
            'id'    => 'count',
            'label' => $this->trans('tl_rateit.rating_chart_legend.3'),
            'type'  => 'number',
        ];

        foreach ($statistics as $obj) {
            $stars = $this->trans($obj->rating === 1 ? 'rateit.star' : 'rateit.stars', [], 'contao_default');

            $arr['rows'][] = [
                'c' => [
                    ['v' => $obj->rating . ' ' . $stars],
                    [
                        'v' => (int) $obj->count,
                        'f' => $obj->count . ' ' . $this->trans('tl_rateit.vote.' . ($obj->count === 1 ? 0 : 1)),
                    ],
                ],
            ];
        }

        return json_encode($arr, JSON_THROW_ON_ERROR);
    }

    private function getMonthsChartData(int $itemId): string
    {
        $arrResult = $this->connection->fetchAllAssociative(
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

        $arrResult = array_reverse($arrResult);

        $arr         = [];
        $arr['cols'] = [];
        $arr['rows'] = [];

        $arr['cols'][] = [
            'id'    => 'month',
            'label' => $this->trans('tl_rateit.month_chart_legend.3'),
            'type'  => 'string',
        ];
        $arr['cols'][] = [
            'id'    => 'count',
            'label' => $this->trans('tl_rateit.month_chart_legend.4'),
            'type'  => 'number',
        ];
        $arr['cols'][] = [
            'id'    => 'avg',
            'label' => $this->trans('tl_rateit.month_chart_legend.2'),
            'type'  => 'number',
        ];

        foreach ($arrResult as $result) {
            $month         = $this->trans('MONTHS.' . ($result['monat'] - 1), [], 'contao_default')
                . ' '
                . $result['jahr'];
            $avgValue      = round((float) ($result['bewertung'] * $this->stars() / 100), 1);
            $arr['rows'][] = ['c' => [['v' => $month], ['v' => (int) $result['anzahl']], ['v' => $avgValue]]];
        }

        return json_encode($arr, JSON_THROW_ON_ERROR);
    }

    private function percentToStars(float $percent): float
    {
        $modifier = (float) (100 / $this->stars());

        return round($percent / $modifier, 1);
    }

    private function stars(): int
    {
        $stars = (int) $this->config()->get('rating_count');

        return $stars > 0 ? $stars : 5;
    }

    /** @return list<string> */
    private function getUsedTypes(): array
    {
        return $this->connection->fetchFirstColumn('SELECT typ FROM tl_rateit_items GROUP BY typ ORDER BY typ');
    }

    /** @return list<string> */
    private function buildPagesList(int $totrecs, int $perpage): array
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

    /** @param array<array-key, mixed> $parameters */
    private function trans(string $key, array $parameters = [], string $domain = 'contao_tl_rateit'): string
    {
        return $this->translator->trans($key, $parameters, $domain);
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
