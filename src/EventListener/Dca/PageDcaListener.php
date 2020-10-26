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

final class PageDcaListener extends BaseDcaListener
{
    protected static $typeName = 'page';

    public function onLoad() : void
    {
        if (! $this->isActive()) {
            return;
        }

        $dca = &$GLOBALS['TL_DCA']['tl_page'];

        $dca['config']['onsubmit_callback'][]          = [self::class, 'onSubmit'];
        $dca['config']['ondelete_callback'][]          = [self::class, 'onDelete'];
        $dca['config']['onrestore_version_callback'][] = [self::class, 'onRestore'];

        $manipulator = PaletteManipulator::create()
            ->addLegend('rateit_legend', '', PaletteManipulator::POSITION_APPEND, true)
            ->addField('addRating', 'rateit_legend', PaletteManipulator::POSITION_APPEND);

        foreach (array_keys($dca['palettes']) as $keyPalette) {
            // Skip if we have a array or the palettes for subselections
            if (in_array($keyPalette, ['__selector__', 'root', 'rootfallback', 'forward', 'redirect'], true)) {
                continue;
            }

            $manipulator->applyToPalette($keyPalette, 'tl_page');
        }
    }
}
