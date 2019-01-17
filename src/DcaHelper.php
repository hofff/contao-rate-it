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
 * Class DcaHelper
 */
class DcaHelper extends \Backend
{

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
	}
   
   /**
	 * Return all navigation templates as array
	 * @param DataContainer
	 * @return array
	 */
	public function getRateItTemplates(\DataContainer $dc)
	{
		$intPid = $dc->activeRecord->pid;

		if ($this->Input->get('act') == 'overrideAll')
		{
			$intPid = $this->Input->get('id');
		}

		return $this->getTemplateGroup('rateit_', $intPid);
	}

	/**
	 * Anlegen eines Datensatzes in der Tabelle tl_rateit_items, falls dieser noch nicht exisitiert.
	 * @param mixed
	 * @param object
	 * @return string
	 */
	public function insertOrUpdateRatingKey(\DC_Table $dc, $type, $ratingTitle) {
		if ($dc->activeRecord->rateit_active || $dc->activeRecord->addRating) {
			$actRecord = $this->Database->prepare("SELECT * FROM tl_rateit_items WHERE rkey=? and typ=?")
							->execute($dc->activeRecord->id, $type)
							->fetchAssoc();
			if (!is_array($actRecord)) {
				$arrSet = array('rkey' => $dc->activeRecord->id,
						'tstamp' => time(),
						'typ' => $type,
						'createdat' => time(),
						'title'=> $ratingTitle,
						'active' => '1'
				);
				$insertRecord = $this->Database->prepare("INSERT INTO tl_rateit_items %s")
											   ->set($arrSet)
											   ->execute()
											   ->insertId;
			} else {
				$this->Database->prepare("UPDATE tl_rateit_items SET active='1', title=? WHERE rkey=? and typ=?")
							   ->execute($ratingTitle, $dc->activeRecord->id, $type)
							   ->updatedId;
			}
		} else {
			$this->Database->prepare("UPDATE tl_rateit_items SET active='' WHERE rkey=? and typ=?")
						   ->execute($dc->activeRecord->id, $type)
						   ->updatedId;
				
		}
		return true;
	}

	/**
	 * Löschen eines Datensatzes aus der Tabelle tl_rateit_items.
	 * @param mixed
	 * @param object
	 * @return string
	 */
	public function deleteRatingKey(\DC_Table $dc, $type)
	{
		$this->Database->prepare("DELETE FROM tl_rateit_items WHERE rkey=? and typ=?")
		               ->execute($dc->activeRecord->id, $type);
		return true;
	}
}
?>
