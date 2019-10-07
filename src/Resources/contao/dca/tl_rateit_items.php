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
$GLOBALS['TL_DCA']['tl_rateit_items'] = array(
    'config' => array(
        'dataContainer' => 'Table',
        'ctable'        => array('tl_rateit_ratings'),
        'switchToEdit'  => false,
        'sql'           => array(
            'keys' => array(
                'id' => 'primary',
            ),
        ),
    ),

    'fields' => array(
        'id'        => array(
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ),
        'tstamp'    => array(
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ),
        'title'     => array(
            'sql' => "varchar(513) NOT NULL default ''",
        ),
        'rkey'      => array(
            'sql' => "varchar(32) NOT NULL default ''",
        ),
        'typ'       => array(
            'sql' => "varchar(32) NOT NULL default ''",
        ),
        'createdat' => array(
            'sql' => "int(10) NOT NULL default '0'",
        ),
        'active'    => array(
            'sql' => "char(1) NOT NULL default ''",
        ),
    ),
);
