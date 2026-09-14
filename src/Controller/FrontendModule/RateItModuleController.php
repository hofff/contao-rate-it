<?php

/**
 * This file is part of hofff/contao-rate-it.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author     David Molineus <david@hofff.com>
 * @author     Carsten Götzinger <info@cgo-it.de>
 * @copyright  2019 hofff.com.
 * @copyright  2013-2018 cgo IT.
 * @license    https://github.com/hofff/contao-rate-it/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace Hofff\Contao\RateIt\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\ModuleModel;
use Hofff\Contao\RateIt\Rating\DetermineCurrentUserId;
use Hofff\Contao\RateIt\Rating\DetermineRatingTemplateName;
use Hofff\Contao\RateIt\Rating\RatingService;
use Override;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsFrontendModule('rateit', category: 'application')]
final class RateItModuleController extends AbstractFrontendModuleController
{
    /** @SuppressWarnings(PHPMD.LongVariable) */
    public function __construct(
        private readonly RatingService $ratingService,
        private readonly DetermineCurrentUserId $determineCurrentUserId,
        private readonly DetermineRatingTemplateName $determineRatingTemplateName,
    ) {
    }

    #[Override]
    public function __invoke(
        Request $request,
        ModuleModel $model,
        string $section,
        array|null $classes = null,
    ): Response {
        if ($this->isBackendScope($request)) {
            return $this->getBackendWildcard($model);
        }

        $template = $this->createTemplate($model, ($this->determineRatingTemplateName)());

        $this->addDefaultDataToTemplate(
            $template,
            $model->row(),
            $section,
            $classes ?? [],
            $request->attributes->get('templateProperties', []),
        );

        $this->tagResponse($model);

        return $this->getResponse($template, $model, $request);
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    #[Override]
    protected function getResponse(FragmentTemplate $template, ModuleModel $model, Request $request): Response
    {
        $rating = $this->ratingService->getRating('module', (int) $model->id, ($this->determineCurrentUserId)());

        if ($rating !== null) {
            $rating['rateit_class'] = $rating['class'];
            unset($rating['class']);
            $template->setData([...$template->getData(), ...$rating]);
        }

        return $template->getResponse();
    }
}
