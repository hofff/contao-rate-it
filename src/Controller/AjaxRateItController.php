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

namespace Hofff\Contao\RateIt\Controller;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Symfony\Component\HttpFoundation\Response;
use Hofff\Contao\RateIt\RateIt;

class AjaxRateItController
{
    /** @var ContaoFrameworkInterface */
    private $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    public function ajaxAction() : Response
    {
        $this->framework->initialize();

        $controller = new RateIt();

        $response = $controller->doVote();
        $response->send();

        return new Response(null);
    }
}
