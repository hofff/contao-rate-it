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

use Contao\Backend;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\DataContainer;

final class ModuleDcaListener extends BaseDcaListener
{
    public function onLoad() : void
    {
        if (! $this->isActive('module')) {
            return;
        }

        $dca = &$GLOBALS['TL_DCA']['tl_module'];

        $dca['config']['onsubmit_callback'][] = [self::class, 'insert'];
        $dca['config']['ondelete_callback'][] = [self::class, 'delete'];

        PaletteManipulator::create()
            ->addLegend('rateit_legend', '', PaletteManipulator::POSITION_APPEND, true)
            ->addField('rateit_active', 'rateit_legend', PaletteManipulator::POSITION_APPEND)
            ->applyToPalette('default', 'tl_module');
    }

    public function insert(DataContainer $dc) : void
    {
        $this->insertOrUpdateRatingKey($dc, 'module', $dc->activeRecord->rateit_title);
    }

    public function delete(DataContainer $dc) : void
    {
        $this->onDeleteItemUpdateRating($dc, 'module');
    }

    public function getRateItTopModuleTemplates() : array
    {
        return Backend::getTemplateGroup('mod_rateit_top');
    }

    public function typeOptions(): array
    {
        return $this->activeItems;
    }
}
