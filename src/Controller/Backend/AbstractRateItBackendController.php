<?php

declare(strict_types=1);

namespace Hofff\Contao\RateIt\Controller\Backend;

use Contao\CoreBundle\Controller\AbstractBackendController;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;

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
}
