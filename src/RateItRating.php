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

class RateItRating extends RateItFrontend { 
	
	/**
	 * RatingKey
	 * @var int
	 */
	public $rkey = 0;
	
	public $ratingType = 'page';
	
	/**
	 * Initialize the controller
	 */
	public function __construct($objElement=array()) {
		parent::__construct($objElement);
	}

	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate() {
		parent::generate();
	}
	
	/**
	 * Compile
	 */
	protected function compile()
	{
		$this->loadLanguageFile('default');

		$this->Template = new \FrontendTemplate($this->strTemplate);
		$this->Template->setData($this->arrData);

		$rating = $this->loadRating($this->rkey, $this->ratingType);
		$ratingId = $this->rkey;
		$stars = !$rating ? 0 : $this->percentToStars($rating['rating']);
		$percent = round($rating['rating'], 0)."%";
		
		$this->Template->descriptionId = 'rateItRating-'.$ratingId.'-description';
		$this->Template->description = $this->getStarMessage($rating);
		$this->Template->id = 'rateItRating-'.$ratingId.'-'.$this->ratingType.'-'.$stars.'_'.$this->intStars;
		$this->Template->class = 'rateItRating';
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
		
		return $this->Template->parse();
	}
	
	public function output() {
	   return $this->compile();
	}
}

?>
