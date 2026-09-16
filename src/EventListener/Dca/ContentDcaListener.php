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

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Override;

#[AsCallback(table: 'tl_content', target: 'config.ondelete', method: 'onDelete')]
#[AsCallback(table: 'tl_content', target: 'config.onrestore_version', method: 'onRestore')]
#[AsCallback(table: 'tl_content', target: 'config.onundo', method: 'onUndo')]
final class ContentDcaListener extends BaseDcaListener
{
    protected static string $typeName = 'ce';

    #[AsCallback(table: 'tl_content', target: 'config.onload')]
    public function onLoad(): void
    {
        if (! $this->isActive()) {
            return;
        }
    }

    #[Override]
    #[AsCallback(table: 'tl_content', target: 'config.onsubmit')]
    public function onSubmit(DataContainer $dataContainer): void
    {
        if (($dataContainer->getCurrentRecord()['type'] ?? null) !== 'rateit') {
            return;
        }

        parent::onSubmit($dataContainer);
    }
}
