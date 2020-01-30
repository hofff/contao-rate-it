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

namespace Hofff\Contao\RateIt\EventListener\Dca;

use Contao\Backend;
use Contao\Database;
use Contao\DataContainer;

/**
 * Class DcaHelper
 */
abstract class BaseDcaListener
{
    /** @var string[] */
    protected $activeItems;

    /**
     * Constructor
     */
    public function __construct(array $activeItems)
    {
        $this->activeItems = $activeItems;
    }

    protected function isActive(string $name) : bool
    {
        return in_array($name, $this->activeItems, true);
    }

    /**
     * Return all navigation templates as array
     * @param DataContainer
     * @return array
     */
    public function getRateItTemplates(DataContainer $dc)
    {
        return Backend::getTemplateGroup('rateit_');
    }

    /**
     * Anlegen eines Datensatzes in der Tabelle tl_rateit_items, falls dieser noch nicht exisitiert.
     * @param mixed
     * @param object
     * @return string
     */
    public function insertOrUpdateRatingKey(DataContainer $dc, $type, $ratingTitle)
    {
        $database = Database::getInstance();

        if ($dc->activeRecord->rateit_active || $dc->activeRecord->addRating) {
            $actRecord = $database->prepare("SELECT * FROM tl_rateit_items WHERE rkey=? and typ=?")
                ->execute($dc->activeRecord->id, $type)
                ->fetchAssoc();
            if (! is_array($actRecord)) {
                $arrSet       = array('rkey'      => $dc->activeRecord->id,
                                      'tstamp'    => time(),
                                      'typ'       => $type,
                                      'createdat' => time(),
                                      'title'     => $ratingTitle,
                                      'active'    => '1',
                );
                $insertRecord = $database->prepare("INSERT INTO tl_rateit_items %s")
                    ->set($arrSet)
                    ->execute()
                    ->insertId;
            } else {
                $database->prepare("UPDATE tl_rateit_items SET active='1', title=? WHERE rkey=? and typ=?")
                    ->execute($ratingTitle, $dc->activeRecord->id, $type)
                    ->updatedId;
            }
        } else {
            $database->prepare("UPDATE tl_rateit_items SET active='' WHERE rkey=? and typ=?")
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
    public function deleteRatingKey(DataContainer $dc, $type)
    {
        Database::getInstance()
            ->prepare('DELETE FROM tl_rateit_items WHERE rkey=? and typ=?')
            ->execute($dc->activeRecord->id, $type);

        return true;
    }
}
