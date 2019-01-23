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

namespace Hofff\Contao\RateIt\EventListener\Hook;

use Contao\FrontendTemplate;
use Contao\LayoutModel;
use Contao\PageModel;

class RateItPageListener extends RatingListener
{
    public function onGeneratePage(PageModel $objPage, LayoutModel $objLayout, $pageHandler) : void
    {
        if (!$objPage->addRating) {
            return;
        }

        $pageTemplate = $pageHandler->Template;
        if (!$pageTemplate) {
            return;
        }

        $template = new FrontendTemplate($this->getRatingTemplate());
        $template->setData((array) $this->getRating('page', $objPage->id));
        $rating = $template->parse();

        if ($objPage->rateit_position == 'after') {
            $pageTemplate->main .= $rating;
        } else {
            $pageTemplate->main = $rating . $template->main;
        }
    }
}
