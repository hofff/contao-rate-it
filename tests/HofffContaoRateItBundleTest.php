<?php

declare(strict_types=1);

namespace Hofff\Contao\RateIt\Test;

use Hofff\Contao\RateIt\HofffContaoRateItBundle;
use PHPUnit\Framework\TestCase;

final class HofffContaoRateItBundleTest extends TestCase
{
    public function testBundleCanBeInstantiated(): void
    {
        self::assertInstanceOf(HofffContaoRateItBundle::class, new HofffContaoRateItBundle());
    }
}
