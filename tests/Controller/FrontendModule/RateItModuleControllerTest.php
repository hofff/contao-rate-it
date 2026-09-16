<?php

declare(strict_types=1);

namespace Hofff\Contao\RateIt\Test\Controller\FrontendModule;

use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\ModuleModel;
use Hofff\Contao\RateIt\Controller\FrontendModule\RateItModuleController;
use Hofff\Contao\RateIt\Rating\DetermineCurrentUserId;
use Hofff\Contao\RateIt\Rating\DetermineRatingTemplateName;
use Hofff\Contao\RateIt\Rating\RatingService;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class RateItModuleControllerTest extends TestCase
{
    public function testMergesRatingDataOntoExistingTemplateData(): void
    {
        $ratingService = $this->createMock(RatingService::class);
        $ratingService->method('getRating')
            ->with('module', 7, null)
            ->willReturn(['class' => 'rateItRating']);

        $determineCurrentUserId = $this->createMock(DetermineCurrentUserId::class);
        $determineCurrentUserId->method('__invoke')->willReturn(null);

        $controller = new RateItModuleController(
            $ratingService,
            $determineCurrentUserId,
            $this->createMock(DetermineRatingTemplateName::class),
        );

        $template = new FragmentTemplate(
            'rateit_default',
            static fn (FragmentTemplate $t, Response|null $r): Response => $r ?? new Response(implode(',', array_keys($t->getData()))),
        );
        $template->setData(['class' => 'mod_rateit']);

        $model = $this->createMock(ModuleModel::class);
        $model->method('__get')->with('id')->willReturn(7);

        $method = new ReflectionMethod($controller, 'getResponse');
        $response = $method->invoke($controller, $template, $model, new Request());

        self::assertSame('class,rateit_class', $response->getContent());
        self::assertSame('rateItRating', $template->getData()['rateit_class']);
    }
}
