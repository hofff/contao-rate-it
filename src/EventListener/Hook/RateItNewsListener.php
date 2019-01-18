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

use Hofff\Contao\RateIt\Frontend\RateItFrontend;

class RateItNewsListener extends RateItFrontend
{

    /**
     * Initialize the controller
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function parseArticle($objTemplate, $objArticle, $caller)
    {
        if (strpos(get_class($caller), "ModuleNews") !== false &&
            $objArticle['addRating']) {
            $ratingId = $objTemplate->id;
            $rating   = $this->loadRating($ratingId, 'news');
            $stars    = ! $rating ? 0 : $this->percentToStars($rating['rating']);
            $percent  = round($rating['rating'], 0) . "%";

            $objTemplate->ratit_template = $this->Config->get('rating_template') ?: 'rateit_default';
            $objTemplate->descriptionId  = 'rateItRating-' . $ratingId . '-description';
            $objTemplate->description    = $this->getStarMessage($rating);
            $objTemplate->ratingId       = 'rateItRating-' . $ratingId . '-news-' . $stars . '_' . $this->intStars;
            $objTemplate->rateit_class   = 'rateItRating';
            $objTemplate->itemreviewed   = $rating['title'];
            $objTemplate->actRating      = $this->percentToStars($rating['rating']);
            $objTemplate->maxRating      = $this->intStars;
            $objTemplate->votes          = $rating['totalRatings'];

            if ($this->strTextPosition == "before") {
                $objTemplate->showBefore = true;
            } else if ($this->strTextPosition == "after") {
                $objTemplate->showAfter = true;
            }

            if ($objArticle['rateit_position'] == 'before') {
                $objTemplate->rateit_rating_before = true;
            } else if ($objArticle['rateit_position'] == 'after') {
                $objTemplate->rateit_rating_after = true;
            }

            $GLOBALS['TL_JAVASCRIPT']['rateit'] = 'bundles/hofffcontaorateit/js/script.js|static';
        }
    }
}
