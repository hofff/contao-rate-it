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

final class NewsDcaListener extends BaseDcaListener
{
    public function onLoad(DataContainer $dataContainer) : void
    {
        if (! $this->isActive('news')) {
            return;
        }

        $dca = &$GLOBALS['TL_DCA']['tl_news'];

        $dca['config']['onsubmit_callback'][] = [self::class, 'insert'];
        $dca['config']['ondelete_callback'][] = [self::class, 'delete'];

        PaletteManipulator::create()
            ->addLegend('rateit_legend', '', PaletteManipulator::POSITION_APPEND, true)
            ->addField('addRating', 'rateit_legend', PaletteManipulator::POSITION_APPEND)
            ->applyToPalette('default', 'tl_news');
    }

    public function insert(DataContainer $dc) : void
    {
        $this->insertOrUpdateRatingKey($dc, 'news', $dc->activeRecord->headline);
    }

    public function delete(DataContainer $dc) : void
    {
        $this->deleteRatingKey($dc, 'news');
    }
}
