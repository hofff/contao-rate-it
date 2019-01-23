<?php

/**
 * This file is part of hofff/contao-content.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author     Carsten Götzinger <info@cgo-it.de>
 * @author     David Molineus <david@hofff.com>
 * @copyright  2013-2018 cgo IT.
 * @copyright  2019 hofff.com.
 * @license    https://github.com/hofff/contao-rate-it/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace Hofff\Contao\RateIt\EventListener\Dca;

use Contao\DataContainer;

final class PageDcaListener extends BaseDcaListener
{
    public function insert(DataContainer $dc) : void
    {
        $this->insertOrUpdateRatingKey($dc, 'page', $dc->activeRecord->title);
    }

    public function delete(DataContainer $dc) : void
    {
        $this->deleteRatingKey($dc, 'page');
    }
}
