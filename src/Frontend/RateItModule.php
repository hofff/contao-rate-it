<?php

declare(strict_types=1);

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

namespace Hofff\Contao\RateIt\Frontend;

use Contao\Model;
use Contao\Model\Collection;
use Override;

/** @psalm-suppress PropertyNotSetInConstructor */
final class RateItModule extends RateItHybrid
{
    public function __construct(Model|Collection|null $objElement = null)
    {
        parent::__construct($objElement);
    }

    #[Override]
    protected function getType(): string
    {
        return 'module';
    }
}
