<?php

declare(strict_types=1);

namespace Hofff\Contao\RateIt\Controller\Backend;

use Hofff\Contao\RateIt\Rating\RatingItemRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

use function explode;

/** @psalm-suppress PropertyNotSetInConstructor */
#[AsController]
final class RateItResetController extends AbstractRateItBackendController
{
    public function __construct(
        private readonly RatingItemRepository $ratingItems,
    ) {
    }

    #[Route(
        '%contao.backend.route_prefix%/rate-it/reset',
        name: 'hofff_contao_rate_it.backend.reset',
        methods: ['POST'],
        defaults: ['_scope' => 'backend'],
    )]
    public function __invoke(Request $request): RedirectResponse
    {
        $this->denyAccessUnlessGrantedForModule();

        if ($request->request->get('rateit_action') === 'updateinformation') {
            $this->ratingItems->updateParentInformation();

            return $this->redirectToList();
        }

        $ids = $request->request->all('selectedids');
        if ($ids === []) {
            return $this->redirectToList();
        }

        $removeParent = $request->request->get('rateit_action') === 'removeratings';

        foreach ($ids as $id) {
            [$rkey, $typ] = explode('__', (string) $id);

            $this->ratingItems->clearRatings($rkey, $typ, $removeParent);
        }

        return $this->redirectToList();
    }
}
