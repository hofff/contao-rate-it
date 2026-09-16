<?php

declare(strict_types=1);

namespace Hofff\Contao\RateIt\Controller\Backend;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\CoreBundle\Controller\AbstractBackendController;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;

use function version_compare;

/** @psalm-suppress PropertyNotSetInConstructor */
abstract class AbstractRateItBackendController extends AbstractBackendController
{
    private const string MODULE_NAME = 'rateit';

    protected function denyAccessUnlessGrantedForModule(): void
    {
        $this->denyAccessUnlessGranted(ContaoCorePermissions::USER_CAN_ACCESS_MODULE, self::MODULE_NAME);
    }

    protected function redirectToList(): RedirectResponse
    {
        return $this->redirect($this->generateUrl('hofff_contao_rate_it.backend.list'));
    }

    /**
     * Reimplements AbstractBackendController::getBackendSessionBag(), which only exists
     * since Contao 5.5 (contao/contao#7683); this package still supports Contao ^5.3.
     */
    protected function sessionBag(Request $request): AttributeBagInterface|null
    {
        $sessionBag = $request->getSession()->getBag('contao_backend');

        return $sessionBag instanceof AttributeBagInterface ? $sessionBag : null;
    }

    /**
     * Contao 5.7 replaced the float-based back end filter panel with a CSS grid
     * (content-filter/content-inner) and swapped its tl_img_submit icon buttons for
     * plain tl_submit ones. The two markups require incompatible DOM positions for
     * the filter's apply/reset buttons, so templates need to branch on the version.
     */
    protected function usesNewFilterLayout(): bool
    {
        return version_compare(ContaoCoreBundle::getVersion(), '5.7.0', '>=');
    }
}
