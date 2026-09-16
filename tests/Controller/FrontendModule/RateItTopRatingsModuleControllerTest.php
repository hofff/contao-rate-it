<?php

declare(strict_types=1);

namespace Hofff\Contao\RateIt\Test\Controller\FrontendModule;

use Contao\Config;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\ModuleModel;
use Doctrine\DBAL\Connection;
use Hofff\Contao\RateIt\Controller\FrontendModule\RateItTopRatingsModuleController;
use Hofff\Contao\RateIt\Rating\RatingService;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class RateItTopRatingsModuleControllerTest extends TestCase
{
    public function testBuildsRatingsFromDatabaseRows(): void
    {
        // 'typ' deliberately does not match a resolveUrl() case ('page'/'article'/
        // 'news'): those branches call e.g. PageModel::findById(), and
        // Contao\Model::__construct()/DcaExtractor need a real Symfony container
        // that this unit test suite does not provide (same class of problem as
        // the ModuleModel container issue noted above). None of this test's
        // assertions read $ratings[0]->url, so exercising the default => null
        // branch keeps the test a pure unit test without weakening any assertion.
        $connection = $this->createMock(Connection::class);
        $connection->method('fetchAllAssociative')->willReturn([
            ['id' => 1, 'rkey' => 10, 'title' => 'Page A', 'typ' => 'unmapped', 'best' => 80.0, 'most' => 3],
        ]);

        $configAdapter = $this->createMock(Adapter::class);
        $configAdapter->method('__call')->with('get', ['rating_count'])->willReturn(5);

        $framework = $this->createMock(ContaoFramework::class);
        $framework->method('getAdapter')->with(Config::class)->willReturn($configAdapter);

        $ratingService = $this->createMock(RatingService::class);
        $ratingService->method('percentToStars')->willReturn(4.0);
        $ratingService->method('getStarMessage')->willReturn('4 of 5 (3 votes)');

        $controller = new RateItTopRatingsModuleController(
            $connection,
            $ratingService,
            $framework,
            $this->createMock(ContentUrlGenerator::class),
        );

        $template = new FragmentTemplate(
            'mod_rateit_top_ratings',
            static fn (FragmentTemplate $t, Response|null $r): Response => $r ?? new Response((string) count($t->get('ratings'))),
        );

        $model = $this->createMock(ModuleModel::class);
        $model->method('__get')->willReturnMap([
            ['rateit_types', serialize(['page'])],
            ['rateit_toptype', 'best'],
            ['rateit_count', 10],
        ]);

        $method = new ReflectionMethod($controller, 'getResponse');
        $response = $method->invoke($controller, $template, $model, new Request());

        self::assertSame('1', $response->getContent());

        $ratings = $template->get('ratings');
        self::assertCount(1, $ratings);
        self::assertSame('Page A', $ratings[0]->title);
        self::assertSame('rateItRating-10-unmapped-4_5', $ratings[0]->rateItID);
        self::assertSame('rateItRating-10-description', $ratings[0]->descriptionId);
        self::assertSame('rateItRating', $ratings[0]->rateit_class);
        self::assertSame('not-rateable', $ratings[0]->rel);
        self::assertSame('4 of 5 (3 votes)', $ratings[0]->description);
    }

    public function testReturnsEmptyListWhenNoTypesAreConfigured(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->expects(self::never())->method('fetchAllAssociative');

        $controller = new RateItTopRatingsModuleController(
            $connection,
            $this->createMock(RatingService::class),
            $this->createMock(ContaoFramework::class),
            $this->createMock(ContentUrlGenerator::class),
        );

        $template = new FragmentTemplate(
            'mod_rateit_top_ratings',
            static fn (FragmentTemplate $t, Response|null $r): Response => $r ?? new Response((string) count($t->get('ratings'))),
        );

        $model = $this->createMock(ModuleModel::class);
        $model->method('__get')->willReturnMap([
            ['rateit_types', serialize([])],
            ['rateit_toptype', 'best'],
            ['rateit_count', 10],
        ]);

        $method = new ReflectionMethod($controller, 'getResponse');
        $response = $method->invoke($controller, $template, $model, new Request());

        self::assertSame('0', $response->getContent());
    }

    public function testDetermineTemplateNameFallsBackWhenFieldIsEmpty(): void
    {
        $controller = new RateItTopRatingsModuleController(
            $this->createMock(Connection::class),
            $this->createMock(RatingService::class),
            $this->createMock(ContaoFramework::class),
            $this->createMock(ContentUrlGenerator::class),
        );

        $model = $this->createMock(ModuleModel::class);
        $model->method('__get')->with('rateit_template')->willReturn('');

        $method = new ReflectionMethod($controller, 'determineTemplateName');
        self::assertSame('mod_rateit_top_ratings', $method->invoke($controller, $model));
    }
}
