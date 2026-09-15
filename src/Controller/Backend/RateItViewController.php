<?php

declare(strict_types=1);

namespace Hofff\Contao\RateIt\Controller\Backend;

use Hofff\Contao\RateIt\Rating\RatingItemRepository;
use stdClass;
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
use function strstr;
use function trim;

use const JSON_THROW_ON_ERROR;

/**
 * Backend UI for viewing the ratings and statistics of a single rated item.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
#[AsController]
final class RateItViewController extends AbstractRateItBackendController
{
    public function __construct(
        private readonly RatingItemRepository $ratingItems,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route(
        '%contao.backend.route_prefix%/rate-it/view',
        name: 'hofff_contao_rate_it.backend.view',
        methods: ['GET', 'POST'],
        defaults: ['_scope' => 'backend'],
    )]
    public function __invoke(Request $request): Response
    {
        $this->denyAccessUnlessGrantedForModule();

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

        $sessionBag = $this->sessionBag($request);

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

        $perpage = $this->ratingItems->perPageSize();

        $options = ['rkey' => $rkey, 'typ' => $typ];
        if ($rateit->f_page >= 0 && $perpage > 0) {
            $options['first'] = $rateit->f_page * $perpage;
            $options['limit'] = $perpage;
        }

        $ratingItems = $this->ratingItems->findRatingItems($options, true);
        if (count($ratingItems) < 1) {
            return $this->redirectToList();
        }

        $ext = $ratingItems[0];

        $ext->ratings = $this->ratingItems->findRatings($ext, $options);
        if ($rateit->f_page >= 0 && $perpage > 0 && count($ext->ratings) === 0) {
            $rateit->f_page   = 0;
            $options['first'] = 0;
            $ext->ratings     = $this->ratingItems->findRatings($ext, $options);
        }

        $totrecs = count($ext->ratings) > 0 ? $ext->ratings[0]->totcount : 0;

        $ext->statistics       = $this->ratingItems->getRatingStatistics((int) $ext->item_id);
        $ext->ratingsChartData = $this->getRatingsChartData($ext->statistics);
        $ext->monthsChartData  = $this->getMonthsChartData((int) $ext->item_id);

        return $this->render('@Contao/backend/rate_it/view.html.twig', [
            'headline'  => $this->trans('tl_rateit.ratings.0'),
            'title'     => $this->trans('tl_rateit.ratings.0'),
            'action'    => $this->generateUrl('hofff_contao_rate_it.backend.view', ['rkey' => $rkey, 'typ' => $typ]),
            'backUrl'   => $this->generateUrl('hofff_contao_rate_it.backend.list'),
            'rateit'    => $rateit,
            'rating'    => $ext,
            'pages'     => $this->ratingItems->buildPagesList($totrecs, $perpage),
            'maxRating' => $this->ratingItems->stars(),
        ]);
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
            $stars = $this->trans($obj->rating === 1 ? 'rateit.star' : 'rateit.stars', 'contao_default');

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
        $arrResult = $this->ratingItems->getMonthsChartRows($itemId);
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
            $month         = $this->trans('MONTHS.' . ($result['monat'] - 1), 'contao_default')
                . ' '
                . $result['jahr'];
            $avgValue      = round((float) ($result['bewertung'] * $this->ratingItems->stars() / 100), 1);
            $arr['rows'][] = ['c' => [['v' => $month], ['v' => (int) $result['anzahl']], ['v' => $avgValue]]];
        }

        return json_encode($arr, JSON_THROW_ON_ERROR);
    }

    private function trans(string $key, string $domain = 'contao_tl_rateit'): string
    {
        $parameters = [];

        return $this->translator->trans($key, $parameters, $domain);
    }
}
