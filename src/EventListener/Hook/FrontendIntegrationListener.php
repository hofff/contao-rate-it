<?php

/**
 * This file is part of hofff/contao-rate-it.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author     David Molineus <david@hofff.com>
 * @copyright  2019 hofff.com.
 * @copyright  2013-2018 cgo IT.
 * @license    https://github.com/hofff/contao-rate-it/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */
declare(strict_types=1);

namespace Hofff\Contao\RateIt\EventListener\Hook;

use Hofff\Contao\RateIt\Frontend\RateItCE;
use Hofff\Contao\RateIt\Frontend\RateItModule;

class FrontendIntegrationListener
{
    /** @var string[] */
    private $activeItems;

    /** @param string[] $activeItems */
    public function __construct(array $activeItems)
    {
        $this->activeItems = $activeItems;
    }

    public function onInitializeSystem() : void
    {
        if (in_array('ce', $this->activeItems, true)) {
            $GLOBALS['TL_CTE']['includes']['rateit'] = RateItCE::class;
        }

        if (in_array('module', $this->activeItems, true)) {
            $GLOBALS['FE_MOD']['application']['rateit'] = RateItModule::class;
        }
    }
}
