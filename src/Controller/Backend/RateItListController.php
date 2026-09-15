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

use function count;
use function is_array;
use function trim;

/**
 * Backend UI for browsing and filtering ratings.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
#[AsController]
final class RateItListController extends AbstractRateItBackendController
{
    public function __construct(
        private readonly RatingItemRepository $ratingItems,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route(
        '%contao.backend.route_prefix%/rate-it',
        name: 'hofff_contao_rate_it.backend.list',
        methods: ['GET', 'POST'],
        defaults: ['_scope' => 'backend'],
    )]
    public function __invoke(Request $request): Response
    {
        $this->denyAccessUnlessGrantedForModule();

        $rateit           = new stdClass();
        $rateit->f_action = 'list';
        $rateit->f_page   = 0;

        $sessionBag = $this->sessionBag($request);

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

        $perpage = $this->ratingItems->perPageSize();

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

        $ratingItems = $this->ratingItems->findRatingItems($options);
        if ($rateit->f_page >= 0 && $perpage > 0 && count($ratingItems) === 0) {
            $rateit->f_page   = 0;
            $options['first'] = 0;
            $ratingItems      = $this->ratingItems->findRatingItems($options);
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
            'headline'    => $this->trans('tl_rateit.ratings.0'),
            'title'       => $this->trans('tl_rateit.ratings.0'),
            'action'      => $this->generateUrl('hofff_contao_rate_it.backend.list'),
            'resetAction' => $this->generateUrl('hofff_contao_rate_it.backend.reset'),
            'rateit'      => $rateit,
            'ratingItems' => $ratingItems,
            'pages'       => $this->ratingItems->buildPagesList($totrecs, $perpage),
            'types'       => $this->ratingItems->getUsedTypes(),
        ]);
    }

    private function trans(string $key): string
    {
        $parameters = [];

        return $this->translator->trans($key, $parameters, 'contao_tl_rateit');
    }
}
