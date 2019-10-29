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
use Contao\DataContainer;

final class PageDcaListener extends BaseDcaListener
{
    public function onLoad() : void
    {
        if (! $this->isActive('page')) {
            return;
        }

        $dca = &$GLOBALS['TL_DCA']['tl_page'];

        $dca['config']['onsubmit_callback'][] = [self::class, 'insert'];
        $dca['config']['ondelete_callback'][] = [self::class, 'delete'];

        $manipulator = PaletteManipulator::create()
            ->addLegend('rateit_legend', '', PaletteManipulator::POSITION_APPEND, true)
            ->addField('addRating', 'rateit_legend', PaletteManipulator::POSITION_APPEND);

        foreach (array_keys($dca['palettes']) as $keyPalette) {
            // Skip if we have a array or the palettes for subselections
            if (in_array($keyPalette, ['__selector__', 'root', 'forward', 'redirect'], true)) {
                continue;
            }

            $manipulator->applyToPalette($keyPalette, 'tl_page');
        }
    }

    public function insert(DataContainer $dc) : void
    {
        $this->insertOrUpdateRatingKey($dc, 'page', $dc->activeRecord->title);
    }

    public function delete(DataContainer $dc) : void
    {
        $this->onDeleteItemUpdateRating($dc, 'page');
    }
}
