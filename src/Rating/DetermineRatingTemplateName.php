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

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFramework;

final class DetermineRatingTemplateName
{
    public function __construct(private readonly ContaoFramework $framework)
    {
    }

    public function __invoke(): string
    {
        $this->framework->initialize();

        $configured = (string) $this->framework->getAdapter(Config::class)->get('rating_template');

        return $configured !== '' ? $configured : 'rateit_default';
    }
}
