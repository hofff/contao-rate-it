<?php

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  cgo IT, 2013
 * @author     Carsten Götzinger (info@cgo-it.de)
 * @package    rateit
 * @license    GNU/LGPL
 * @filesource
 */

namespace Hofff\Contao\RateIt;

class RateItNews extends RateItFrontend { 
	
	/**
	 * Initialize the controller
	 */
	public function __construct() {
		parent::__construct();
	}
	
	public function parseArticle($objTemplate, $objArticle, $caller) {
		if (strpos(get_class($caller), "ModuleNews") !== false &&
				$objArticle['addRating']) { 
			   $ratingId = $objTemplate->id;
	   		$rating = $this->loadRating($ratingId, 'news');
	   		$stars = !$rating ? 0 : $this->percentToStars($rating['rating']);
	   		$percent = round($rating['rating'], 0)."%";
	   		
	   		$objTemplate->descriptionId = 'rateItRating-'.$ratingId.'-description';
	   		$objTemplate->description = $this->getStarMessage($rating);
	   		$objTemplate->ratingId = 'rateItRating-'.$ratingId.'-news-'.$stars.'_'.$this->intStars;
			   $objTemplate->rateit_class = 'rateItRating';
	   		$objTemplate->itemreviewed = $rating['title'];
	   		$objTemplate->actRating = $this->percentToStars($rating['rating']);
	   		$objTemplate->maxRating = $this->intStars;
	   		$objTemplate->votes = $rating[totalRatings];
	   		
	   		if ($this->strTextPosition == "before") {
	   			$objTemplate->showBefore = true;
	   		}
	   		else if ($this->strTextPosition == "after") {
	   			$objTemplate->showAfter = true;
			}
				
			if ($objArticle['rateit_position'] == 'before') {
				$objTemplate->rateit_rating_before = true;
			} else if ($objArticle['rateit_position'] == 'after') {
				$objTemplate->rateit_rating_after = true;
			}
			
			$GLOBALS['TL_JAVASCRIPT'][] = 'bundles/cgoitrateit/js/onReadyRateIt.js|static';
			$GLOBALS['TL_JAVASCRIPT'][] = 'bundles/cgoitrateit/js/rateit.js|static';
	   		$GLOBALS['TL_CSS'][] = 'bundles/cgoitrateit/css/rateit.min.css||static';
			switch ($GLOBALS['TL_CONFIG']['rating_type']) {
				case 'hearts' :
					$GLOBALS['TL_CSS'][] = 'bundles/cgoitrateit/css/heart.min.css||static';
					break;
				default:
					$GLOBALS['TL_CSS'][] = 'bundles/cgoitrateit/css/star.min.css||static';
			}
		}
	}
}
?>
