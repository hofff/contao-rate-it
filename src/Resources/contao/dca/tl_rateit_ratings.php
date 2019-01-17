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
 * @copyright  2012-2019 hofff.com
 * @license    https://github.com/hofff/contao-rate-it/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

/**
 * Table tl_rateit_items
 */
$GLOBALS['TL_DCA']['tl_rateit_ratings'] = array(
    'config' => array(
        'dataContainer' => 'Table',
        'ptable'        => 'tl_rateit_items',
        'switchToEdit'  => false,
        'sql'           => array(
            'keys' => array(
                'id'  => 'primary',
                'pid' => 'index',
            ),
        ),
    ),

    'fields' => array(
        'id'         => array(
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ),
        'tstamp'     => array(
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ),
        'pid'        => array(
            'foreignKey' => 'tl_rateit_items.id',
            'sql'        => "int(10) unsigned NOT NULL default '0'",
            'relation'   => array('type' => 'belongsTo', 'load' => 'lazy'),
        ),
        'ip_address' => array(
            'sql' => "varchar(50) NULL",
        ),
        'memberid'   => array(
            'sql' => "int(10) unsigned NULL",
        ),
        'rating'     => array(
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ),
        'createdat'  => array(
            'sql' => "int(10) NOT NULL default '0'",
        ),
    ),
);
