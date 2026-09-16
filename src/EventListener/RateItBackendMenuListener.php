<?php

declare(strict_types=1);

namespace Hofff\Contao\RateIt\EventListener;

use Contao\CoreBundle\Event\ContaoCoreEvents;
use Contao\CoreBundle\Event\MenuEvent;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function str_starts_with;

#[AsEventListener(ContaoCoreEvents::BACKEND_MENU_BUILD)]
final class RateItBackendMenuListener
{
    public function __construct(
        private readonly Security $security,
        private readonly RouterInterface $router,
        private readonly RequestStack $requestStack,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function __invoke(MenuEvent $event): void
    {
        if ($event->getTree()->getName() !== 'mainMenu') {
            return;
        }

        if (! $this->security->isGranted(ContaoCorePermissions::USER_CAN_ACCESS_MODULE, 'rateit')) {
            return;
        }

        $contentNode = $event->getTree()->getChild('content');
        if ($contentNode === null) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();

        $node = $event->getFactory()
            ->createItem('rateit')
            ->setLabel($this->translator->trans('MOD.rateit.0', [], 'contao_modules'))
            ->setUri($this->router->generate('hofff_contao_rate_it.backend.list'))
            ->setLinkAttribute('class', 'navigation rateit')
            ->setLinkAttribute('title', $this->translator->trans('MOD.rateit.1', [], 'contao_modules'))
            ->setLinkAttribute('data-contao--tooltips-target', 'tooltip')
            ->setExtra('translation_domain', false);

        if (
            $request !== null
            && str_starts_with((string) $request->attributes->get('_route'), 'hofff_contao_rate_it.backend.')
        ) {
            $node->setCurrent(true);
        }

        $contentNode->addChild($node);
    }
}
