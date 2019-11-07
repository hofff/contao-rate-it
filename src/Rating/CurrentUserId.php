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

namespace Hofff\Contao\RateIt\Rating;

use function uniqid;

final class CurrentUserId
{
    /** @var string */
    private $value;

    public function __construct()
    {
        if (isset($_COOKIE['hofff_rate_it'])) {
            $this->value = $_COOKIE['hofff_rate_it'];

            return;
        }

        $this->value = uniqid('', true);
        setcookie('hofff_rate_it', $this->value, time() + 31536000);
    }

    public function __toString() : string
    {
        return $this->value;
    }

}
