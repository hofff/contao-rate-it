<?php

declare(strict_types=1);

namespace Hofff\Contao\RateIt\Test\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Hofff\Contao\RateIt\Controller\ContentElement\RateItContentElementController;
use Hofff\Contao\RateIt\Rating\DetermineCurrentUserId;
use Hofff\Contao\RateIt\Rating\DetermineRatingTemplateName;
use Hofff\Contao\RateIt\Rating\RatingService;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class RateItContentElementControllerTest extends TestCase
{
    public function testMergesRatingDataOntoExistingTemplateData(): void
    {
        $ratingService = $this->createMock(RatingService::class);
        $ratingService->method('getRating')
            ->with('ce', 5, 42)
            ->willReturn(['class' => 'rateItRating', 'actRating' => 4.5]);

        $determineCurrentUserId = $this->createMock(DetermineCurrentUserId::class);
        $determineCurrentUserId->method('__invoke')->willReturn(42);

        $controller = new RateItContentElementController(
            $ratingService,
            $determineCurrentUserId,
            $this->createMock(DetermineRatingTemplateName::class),
        );

        $template = new FragmentTemplate(
            'rateit_default',
            static fn (FragmentTemplate $t, Response|null $r): Response => $r ?? new Response(implode(',', array_keys($t->getData()))),
        );
        $template->setData(['class' => 'ce_rateit', 'cssID' => ' id="foo"']);

        $model = $this->createMock(ContentModel::class);
        $model->method('__get')->with('id')->willReturn(5);

        $method = new ReflectionMethod($controller, 'getResponse');
        $response = $method->invoke($controller, $template, $model, new Request());

        self::assertSame('class,cssID,actRating,rateit_class', $response->getContent());
        self::assertSame('rateItRating', $template->getData()['rateit_class']);
    }

    public function testDoesNotTouchTemplateDataWhenNoRatingExists(): void
    {
        $ratingService = $this->createMock(RatingService::class);
        $ratingService->method('getRating')->willReturn(null);

        $determineCurrentUserId = $this->createMock(DetermineCurrentUserId::class);
        $determineCurrentUserId->method('__invoke')->willReturn(null);

        $controller = new RateItContentElementController(
            $ratingService,
            $determineCurrentUserId,
            $this->createMock(DetermineRatingTemplateName::class),
        );

        $template = new FragmentTemplate(
            'rateit_default',
            static fn (FragmentTemplate $t, Response|null $r): Response => $r ?? new Response(implode(',', array_keys($t->getData()))),
        );
        $template->setData(['class' => 'ce_rateit']);

        $model = $this->createMock(ContentModel::class);
        $model->method('__get')->with('id')->willReturn(5);

        $method = new ReflectionMethod($controller, 'getResponse');
        $response = $method->invoke($controller, $template, $model, new Request());

        self::assertSame('class', $response->getContent());
    }
}
