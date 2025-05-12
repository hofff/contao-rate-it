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

declare(strict_types=1);

/**
 * Table tl_rateit_items
 */
$GLOBALS['TL_DCA']['tl_rateit_ratings'] = [
    'config' => [
        'dataContainer' => 'Table',
        'ptable'        => 'tl_rateit_items',
        'switchToEdit'  => false,
        'sql'           => [
            'keys' => [
                'id'  => 'primary',
                'pid' => 'index',
            ],
        ],
    ],

    'fields' => [
        'id'         => [
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ],
        'tstamp'     => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'pid'        => [
            'foreignKey' => 'tl_rateit_items.id',
            'sql'        => "int(10) unsigned NOT NULL default '0'",
            'relation'   => ['type' => 'belongsTo', 'load' => 'lazy'],
        ],
        'session_id' => [
            'sql' => "varchar(255) NULL",
        ],
        'memberid'   => [
            'sql' => "int(10) unsigned NULL",
        ],
        'rating'     => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'createdat'  => [
            'sql' => "int(10) NOT NULL default '0'",
        ],
    ],
];
