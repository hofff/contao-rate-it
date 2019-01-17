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

/**
 * Class RateItHybrid
 */
abstract class RateItHybrid extends RateItFrontend
{
	//protected $intStars = 5;
	
	/**
	 * Initialize the controller
	 */
	public function __construct($objElement) {
		parent::__construct($objElement);
	}

	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate() {
		if (TL_MODE == 'BE') {
			$objTemplate = new \BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### Rate IT ###';
			$objTemplate->title = $this->rateit_title;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}
		
		$this->strTemplate = $GLOBALS['TL_CONFIG']['rating_template'];
		
		$this->strType = $GLOBALS['TL_CONFIG']['rating_type'];
		$this->strTextPosition = $GLOBALS['TL_CONFIG']['rating_textposition'];

      $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/cgoitrateit/js/onReadyRateIt.js|static';
		$GLOBALS['TL_JAVASCRIPT'][] = 'bundles/cgoitrateit/js/rateit.js|static';
		$GLOBALS['TL_CSS'][] = 'bundles/cgoitrateit/css/rateit.min.css||static';
		switch ($this->strType) {
			case 'hearts' :
				$GLOBALS['TL_CSS'][] = 'bundles/cgoitrateit/css/heart.min.css||static';
				break;
			default:
				$GLOBALS['TL_CSS'][] = 'bundles/cgoitrateit/css/star.min.css||static';
		}
		
		return parent::generate();
	}

	/**
	 * Generate the module/content element
	 */
	protected function compile() {
		$this->Template = new \FrontendTemplate($this->strTemplate);
		
		$this->Template->setData($this->arrData);

   		$rating = $this->loadRating($this->getParent()->id, $this->getType());
   		$ratingId = $this->getParent()->id;
   		$stars = !$rating ? 0 : $this->percentToStars($rating['rating']);
   		$percent = round($rating['rating'], 0)."%";
   		
   		$this->Template->descriptionId = 'rateItRating-'.$ratingId.'-description';
   		$this->Template->description = $this->getStarMessage($rating);
   		$this->Template->id = 'rateItRating-'.$ratingId.'-'.$this->getType().'-'.$stars.'_'.$this->intStars;
   		$this->Template->rateit_class = 'rateItRating';
   		$this->Template->itemreviewed = $rating['title'];
   		$this->Template->actRating = $this->percentToStars($rating['rating']);
   		$this->Template->maxRating = $this->intStars;
   		$this->Template->votes = $rating[totalRatings];
   		
   		if ($this->strTextPosition == "before") {
   			$this->Template->showBefore = true;
   		}
   		else if ($this->strTextPosition == "after") {
   			$this->Template->showAfter = true;
   		}
   		
   		return parent::compile();
	}
}

?>
