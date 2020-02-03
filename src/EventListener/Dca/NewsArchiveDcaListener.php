<?php

/**
 * This file is part of hofff/contao-rate-it.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author     David Molineus <david@hofff.com>
 * @copyright  2019-2020 hofff.com.
 * @license    https://github.com/hofff/contao-rate-it/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace Hofff\Contao\RateIt\EventListener\Dca;

use Contao\CoreBundle\DataContainer\PaletteManipulator;

final class NewsArchiveDcaListener extends BaseDcaListener
{
    protected static $typeName = 'comments';

    public function onLoad() : void
    {
        if (! $this->isActive()) {
            return;
        }

        PaletteManipulator::create()
            ->addField('addCommentsRating', 'allowComments', PaletteManipulator::POSITION_APPEND)
            ->applyToPalette('default', 'tl_news_archive');
    }
}
