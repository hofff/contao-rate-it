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

use Contao\DataContainer;

final class ContentDcaListener extends BaseDcaListener
{
    public function onLoad() : void
    {
        if (! $this->isActive('ce')) {
            return;
        }

        $dca = &$GLOBALS['TL_DCA']['tl_content'];

        $dca['config']['onsubmit_callback'][]          = [self::class, 'insert'];
        $dca['config']['ondelete_callback'][]          = [self::class, 'delete'];
        $dca['config']['onrestore_version_callback'][] = [self::class, 'onRestore'];
    }

    public function insert(DataContainer $dc) : void
    {
        if ($dc->activeRecord->type !== 'rateit') {
            return;
        }

        // FIXME: insertOrUpdateRatingKey for tl_content can't work because no addRating flag exists
        $this->insertOrUpdateRatingKey($dc, 'ce', $dc->activeRecord->rateit_title, $dc->activeRecord->published);
    }

    public function delete(DataContainer $dc) : void
    {
        $this->onDeleteItemUpdateRating($dc, 'ce');
    }

    public function onRestore(string $table, $insertId, $version, array $data) : void
    {
        $this->restore($insertId, 'ce', !$data['invisible']);
    }

    public function onUndo(string $table, array $row) : void
    {
        if (! $this->isActive('ce')) {
            return;
        }

        $this->restore($row['id'], 'ce', !$row['invisibile']);
    }
}
