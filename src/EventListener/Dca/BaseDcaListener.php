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
     *
     * @param mixed
     * @param object
     * @return bool
     */
    public function insertOrUpdateRatingKey(DataContainer $dc, $type, $ratingTitle, $published)
    {
        $database     = Database::getInstance();
        $parentstatus = $published == '1' ? 'a' : 'i';

        if ($dc->activeRecord->rateit_active || $dc->activeRecord->addRating) {
            $actRecord = $database->prepare("SELECT * FROM tl_rateit_items WHERE rkey=? and typ=?")
                ->execute($dc->activeRecord->id, $type)
                ->fetchAssoc();
            if (! is_array($actRecord)) {
                $arrSet       = array('rkey'         => $dc->activeRecord->id,
                                      'tstamp'       => time(),
                                      'typ'          => $type,
                                      'createdat'    => time(),
                                      'title'        => $ratingTitle,
                                      'active'       => '1',
                                      'parentstatus' => $parentstatus,
                );
                $database->prepare("INSERT INTO tl_rateit_items %s")
                    ->set($arrSet)
                    ->execute();
            } else {
                $database->prepare("UPDATE tl_rateit_items SET active='1', title=?, parentstatus=? WHERE rkey=? and typ=?")
                    ->execute($ratingTitle, $parentstatus, $dc->activeRecord->id, $type);
            }
        } else {
            $database->prepare("UPDATE tl_rateit_items SET active='', parentstatus=? WHERE rkey=? and typ=?")
                ->execute($parentstatus, $dc->activeRecord->id, $type);
        }

        return true;
    }

    /**
     * Updates the rating when the parent item has been deleted.
     * 
     * @param DataContainer $dc Provides the current active item.
     * @param string $type Contao type, e. g. news
     * @return boolean Always true
     */
    public function onDeleteItemUpdateRating(DataContainer $dc, $type)
    {
        Database::getInstance()
            ->prepare("UPDATE tl_rateit_items SET parentstatus = 'r' WHERE rkey=? and typ=?")
            ->execute($dc->activeRecord->id, $type);

        return true;
    }

    public function restore($rkey, $type, $published)
    {
        Database::getInstance()
            ->prepare('UPDATE tl_rateit_items %s WHERE rkey=? and typ=?')
            ->set(['parentstatus' => $published ? 'a' : 'i'])
            ->execute($rkey, $type);
    }
}
