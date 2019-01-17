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
 * Class RateItFrontend
 */
class RateItFrontend extends \Hybrid
{

	/**
	 * Primary key
	 * @var string
	 */
	protected $strPk = 'id';

	/**
	 * Typ
	 * @var string
	 */
	protected $strType = 'hearts';

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'rateit_default';

	/**
	 * Anzahl der Herzen/Sterne
	 * @var int
	 */
	protected $intStars = 5;

	/**
	 * Textposition
	 * @var string
	 */
	protected $strTextPosition = 'after';

	/**
	 * Initialize the controller
	 */
	public function __construct($objElement=array()) {
		if (!empty($objElement)) {
			if ($objElement instanceof \Model) {
				$this->strTable = $objElement->getTable();
			}
			elseif ($objElement instanceof \Model\Collection) {
				$this->strTable = $objElement->current()->getTable();
			}

			$this->strKey = $this->strPk;
		}

		$stars = intval($GLOBALS['TL_CONFIG']['rating_count']);
		if ($stars > 0) {
			$this->intStars = $stars;
		}
		parent::__construct($objElement);
	}

	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate() {
	   return parent::generate();
	   $this->loadLanguageFile('default');
	   $this->strType = $GLOBALS['TL_CONFIG']['rating_type'];
	   $stars = intval($GLOBALS['TL_CONFIG']['rating_count']);
	   if ($stars > 0) {
		$this->intStars = $stars;
	   }
	   $this->strTemplate = $GLOBALS['TL_CONFIG']['rating_template'];
	   $this->strTextPosition = $GLOBALS['TL_CONFIG']['rating_textposition'];
	}


	/**
	 * Generate the module/content element
	 */
	protected function compile() {
	}

	public function getStarMessage($rating) {
		$this->loadLanguageFile('default');
		$stars = $this->percentToStars($rating['rating']);
		preg_match('/^.*\[(.+)\|(.+)\].*$/i', $GLOBALS['TL_CONFIG']['rating_description'], $labels);
		if (!is_array($labels) && (!count($labels) == 2 || !count($labels) == 3)) {
			$label = ($rating[totalRatings] > 1 || $rating[totalRatings] == 0) || !$rating ? $GLOBALS['TL_LANG']['rateit']['rating_label'][1] : $GLOBALS['TL_LANG']['rateit']['rating_label'][0];
			$description = '%current%/%max% %type% (%count% ['.$GLOBALS['TL_LANG']['tl_rateit']['vote'][0].'|'.$GLOBALS['TL_LANG']['tl_rateit']['vote'][1].'])';
		} else {
			$label = count($labels) == 2 ? $labels[1] : ($rating[totalRatings] > 1 || $rating[totalRatings] == 0) || !$rating ? $labels[2] : $labels[1];
			$description = $GLOBALS['TL_CONFIG']['rating_description'];
		}
		$actValue = $rating === false ? 0 : $rating[totalRatings];
		$type = $GLOBALS['TL_CONFIG']['rating_type'] == 'hearts' ? $GLOBALS['TL_LANG']['rateit']['hearts'] : $GLOBALS['TL_LANG']['rateit']['stars'];
// 		return str_replace('.', ',', $stars)."/$this->intStars ".$type." ($actValue $label)";
		$description = str_replace('%current%', str_replace('.', ',', $stars), $description);
		$description = str_replace('%max%', $this->intStars, $description);
		$description = str_replace('%type%', $type, $description);
		$description = str_replace('%count%', $actValue, $description);
		$description = preg_replace('/^(.*)(\[.*\])(.*)$/i', "\\1$label\\3", $description);
		return $description;
	}

	public function loadRating($rkey, $typ) {
		$SQL_GET_RATINGS = "SELECT i.rkey AS rkey,
			i.title AS title,
			IFNULL(AVG(r.rating),0) AS rating,
			COUNT( r.rating ) AS totalRatings
			FROM tl_rateit_items i
			LEFT OUTER JOIN tl_rateit_ratings r
			ON ( i.id = r.pid ) WHERE i.rkey = ? and typ=? and active='1'
			GROUP BY i.rkey, i.title;";
		$result = $this->Database->prepare($SQL_GET_RATINGS)
							->execute($rkey, $typ)
							->fetchAssoc();
		return $result;
	}

	protected function percentToStars($percent) {
		$modifier = 100 / $this->intStars;
		return round($percent / $modifier, 1);
	}
}

?>
