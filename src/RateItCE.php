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
 * @copyright  2012-2019 hofff.com
 * @license    https://github.com/hofff/contao-rate-it/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace Hofff\Contao\RateIt;

/**
 * Class RateItCE
 */
class RateItCE extends RateItHybrid
{

    /**
     * Initialize the controller
     */
    public function __construct($objElement)
    {
        parent::__construct($objElement);
    }

    protected function getType()
    {
        return 'ce';
    }
}
