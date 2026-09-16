<?php

declare(strict_types=1);

namespace Hofff\Contao\RateIt\Test\Rating;

use Contao\Config;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFramework;
use Doctrine\DBAL\Connection;
use Hofff\Contao\RateIt\Rating\IsUserAllowedToRate;
use Hofff\Contao\RateIt\Rating\RatingService;
use PHPUnit\Framework\TestCase;

final class RatingServiceTest extends TestCase
{
    public function testPercentToStarsConvertsPercentageUsingConfiguredMaxStars(): void
    {
        $configAdapter = $this->createMock(Adapter::class);
        $configAdapter->method('__call')->with('get', ['rating_count'])->willReturn(5);

        $framework = $this->createMock(ContaoFramework::class);
        $framework->method('getAdapter')->with(Config::class)->willReturn($configAdapter);

        $connection = $this->createMock(Connection::class);

        $service = new RatingService(
            $connection,
            $framework,
            new IsUserAllowedToRate($connection, $framework),
        );

        self::assertSame(2.5, $service->percentToStars(50.0));
    }
}
