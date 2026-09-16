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

namespace Hofff\Contao\RateIt\EventListener\Dca;

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;

use function array_keys;
use function assert;
use function in_array;
use function is_string;

#[AsCallback(table: 'tl_page', target: 'config.onsubmit', method: 'onSubmit')]
#[AsCallback(table: 'tl_page', target: 'config.ondelete', method: 'onDelete')]
#[AsCallback(table: 'tl_page', target: 'config.onrestore_version', method: 'onRestore')]
#[AsCallback(table: 'tl_page', target: 'config.onundo', method: 'onUndo')]
final class PageDcaListener extends BaseDcaListener
{
    protected static string $typeName = 'page';

    #[AsCallback(table: 'tl_page', target: 'config.onload')]
    public function onLoad(): void
    {
        if (! $this->isActive()) {
            return;
        }

        $dca = &$GLOBALS['TL_DCA']['tl_page'];

        $manipulator = PaletteManipulator::create()
            ->addLegend('rateit_legend', '', PaletteManipulator::POSITION_APPEND, true)
            ->addField('addRating', 'rateit_legend', PaletteManipulator::POSITION_APPEND);

        foreach (array_keys($dca['palettes']) as $keyPalette) {
            /** @psalm-suppress TypeDoesNotContainType */
            assert(is_string($keyPalette));

            // Skip if we have an array or the palettes for subselections
            if (in_array($keyPalette, ['__selector__', 'root', 'rootfallback', 'forward', 'redirect'], true)) {
                continue;
            }

            /** @psalm-suppress NoValue */
            $manipulator->applyToPalette($keyPalette, 'tl_page');
        }
    }
}
