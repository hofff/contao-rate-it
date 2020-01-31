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

use Contao\Database;
use Contao\DataContainer;
use Hofff\Contao\RateIt\Rating\RatingTypes;

/**
 * Class DcaHelper
 */
abstract class BaseDcaListener
{
    /** @var RatingTypes */
    protected $ratingTypes;

    /** @var string */
    protected static $typeName;

    /**
     * Constructor
     */
    public function __construct(RatingTypes $ratingTypes)
    {
        $this->ratingTypes = $ratingTypes;

        if (!isset(static::$typeName)) {
            throw new \RuntimeException('Type name has to be defined');
        }
    }

    protected function isActive() : bool
    {
        return $this->ratingTypes->has(static::$typeName);
    }

    public function onSubmit(DataContainer $dc) : void
    {
        $this->insertOrUpdateRatingKey($dc);
    }

    public function onDelete(DataContainer $dc) : void
    {
        $this->markRatingItemAsDeleted($dc);
    }

    public function onRestore(string $table, $insertId) : void
    {
        $this->restore((int) $insertId);
    }

    public function onUndo(string $table, array $row) : void
    {
        if (! $this->isActive()) {
            return;
        }

        $this->restore((int) $row['id']);
    }

    /**
     * Anlegen eines Datensatzes in der Tabelle tl_rateit_items, falls dieser noch nicht exisitiert.
     *
     * @param mixed
     * @param object
     * @return void
     */
    public function insertOrUpdateRatingKey(DataContainer $dc)
    {
        $database     = Database::getInstance();
        $sourceId     = (int) $dc->id;
        $information  = $this->ratingTypes->sourceInformation(static::$typeName, $sourceId);

        if (!$information) {
            return;
        }

        if ($information->active()) {
            $actRecord = $database
                ->prepare('SELECT * FROM tl_rateit_items WHERE rkey=? and typ=? LIMIT 0,1')
                ->execute($dc->activeRecord->id, static::$typeName)
                ->fetchAssoc();

            if (! is_array($actRecord)) {
                $arrSet = [
                    'rkey'         => $dc->activeRecord->id,
                    'tstamp'       => time(),
                    'typ'          => static::$typeName,
                    'createdat'    => time(),
                    'title'        => $information->title(),
                    'active'       => '1',
                    'parentstatus' => $information->parentStatus(),
                ];

                $database
                    ->prepare('INSERT INTO tl_rateit_items %s')
                    ->set($arrSet)
                    ->execute();
            } else {
                $database
                    ->prepare("UPDATE tl_rateit_items SET active='1', title=?, parentstatus=? WHERE rkey=? and typ=?")
                    ->execute($information->title(), $information->parentStatus(), $dc->id, static::$typeName);
            }
        } else {
            $database
                ->prepare("UPDATE tl_rateit_items SET active='', parentstatus=? WHERE rkey=? and typ=?")
                ->execute($information->parentStatus(), $dc->id, static::$typeName);
        }
    }

    public function updateRatingKey(int $sourceId) : void
    {
        $information  = $this->ratingTypes->sourceInformation(static::$typeName, $sourceId);
        if (!$information) {
            return;
        }

        Database::getInstance()
            ->prepare('UPDATE tl_rateit_items SET title=?, parentstatus=? WHERE rkey=? and typ=?')
            ->execute($information->title(), $information->parentStatus(), $sourceId, static::$typeName);
    }

    /**
     * Updates the rating when the parent item has been deleted.
     * 
     * @param DataContainer $dc Provides the current active item.
     */
    public function markRatingItemAsDeleted(DataContainer $dc): void
    {
        Database::getInstance()
            ->prepare("UPDATE tl_rateit_items SET parentstatus = 'r' WHERE rkey=? and typ=?")
            ->execute($dc->activeRecord->id, static::$typeName);
    }

    public function restore(int $statusId)
    {
        $information = $this->ratingTypes->sourceInformation(static::$typeName, $statusId);
        if (!$information) {
            return;
        }

        Database::getInstance()
            ->prepare('UPDATE tl_rateit_items %s WHERE rkey=? and typ=?')
            ->set(['parentstatus' => $information->parentStatus()])
            ->execute($statusId, static::$typeName);
    }
}
