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

#[AsCallback(table: 'tl_article', target: 'config.onsubmit', method: 'onSubmit')]
#[AsCallback(table: 'tl_article', target: 'config.ondelete', method: 'onDelete')]
final class ArticleDcaListener extends BaseDcaListener
{
    protected static string $typeName = 'article';

    #[AsCallback(table: 'tl_article', target: 'config.onload')]
    public function onLoad(): void
    {
        if (! $this->isActive()) {
            return;
        }

        PaletteManipulator::create()
            ->addLegend('rateit_legend', '', PaletteManipulator::POSITION_APPEND, true)
            ->addField('addRating', 'rateit_legend', PaletteManipulator::POSITION_APPEND)
            ->applyToPalette('default', 'tl_article');
    }
}
