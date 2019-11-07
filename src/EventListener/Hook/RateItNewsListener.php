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

use Contao\ModuleNews;
use Contao\Template;

final class RateItNewsListener extends RatingListener
{
    public function onParseArticles(Template $template, array $newsArticle, $caller) : void
    {
        if (!$caller instanceof ModuleNews || !$newsArticle['addRating']) {
            return;
        }

        $template->ratit_template = $this->getRatingTemplate();
        $template->rating         = $this->getRating('news', (int) $template->id);
    }
}
