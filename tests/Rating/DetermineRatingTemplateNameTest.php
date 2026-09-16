<?php

declare(strict_types=1);

namespace Hofff\Contao\RateIt\Test\Rating;

use Contao\Config;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFramework;
use Hofff\Contao\RateIt\Rating\DetermineRatingTemplateName;
use PHPUnit\Framework\TestCase;

final class DetermineRatingTemplateNameTest extends TestCase
{
    public function testReturnsConfiguredTemplateName(): void
    {
        $configAdapter = $this->createMock(Adapter::class);
        $configAdapter->method('__call')->with('get', ['rating_template'])->willReturn('my_custom_rateit');

        $framework = $this->createMock(ContaoFramework::class);
        $framework->method('getAdapter')->with(Config::class)->willReturn($configAdapter);

        $determineRatingTemplateName = new DetermineRatingTemplateName($framework);

        self::assertSame('my_custom_rateit', $determineRatingTemplateName());
    }

    public function testFallsBackToRateitDefaultWhenConfigValueIsEmpty(): void
    {
        $configAdapter = $this->createMock(Adapter::class);
        $configAdapter->method('__call')->with('get', ['rating_template'])->willReturn('');

        $framework = $this->createMock(ContaoFramework::class);
        $framework->method('getAdapter')->with(Config::class)->willReturn($configAdapter);

        $determineRatingTemplateName = new DetermineRatingTemplateName($framework);

        self::assertSame('rateit_default', $determineRatingTemplateName());
    }
}
