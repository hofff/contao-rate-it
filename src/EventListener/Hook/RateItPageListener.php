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

namespace Hofff\Contao\RateIt\EventListener\Hook;

use Contao\FrontendTemplate;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\PageRegular;

final class RateItPageListener extends RatingListener
{
    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function onGeneratePage(PageModel $pageModel, LayoutModel $layoutModel, PageRegular $pageHandler): void
    {
        if (! $pageModel->addRating || $pageModel->rateit_position === 'custom') {
            return;
        }

        if (! isset($pageModel->Template)) {
            return;
        }

        $template = new FrontendTemplate($this->getRatingTemplate());
        $template->setData((array) $this->getRating('page', $pageModel->id));
        $rating = $template->parse();

        if ($pageModel->rateit_position === 'after') {
            $pageModel->Template->main .= $rating;
        } else {
            $pageModel->Template->main = $rating . $pageModel->Template->main;
        }
    }
}
