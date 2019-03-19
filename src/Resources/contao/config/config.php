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
 * @copyright  2019 hofff.com.
 * @license    https://github.com/hofff/contao-rate-it/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

use Hofff\Contao\RateIt\Backend\RateItBackend;
use Hofff\Contao\RateIt\Backend\RateItBackendModule;
use Hofff\Contao\RateIt\Frontend\RateItCE;
use Hofff\Contao\RateIt\Frontend\RateItModule;
use Hofff\Contao\RateIt\Frontend\RateItTopRatingsModule;

/**
 * Back end modules
 */
array_insert(
    $GLOBALS['BE_MOD']['content'],
    -1,
    [
        'rateit' => [
            'callback'   => RateItBackendModule::class,
            'icon'       => RateItBackend::image('icon'),
            'stylesheet' => RateItBackend::css('backend'),
            'javascript' => RateItBackend::js('RateItBackend'),
        ],
    ]
);

/**
 * frontend moduls
 */
$GLOBALS['FE_MOD']['application']['rateit']             = RateItModule::class;
$GLOBALS['FE_MOD']['application']['rateit_top_ratings'] = RateItTopRatingsModule::class;

/**
 * content elements
 */
$GLOBALS['TL_CTE']['includes']['rateit'] = RateItCE::class;

/*
 * Default configuration
 */
$GLOBALS['TL_CONFIG']['rating_count']        = 5;
$GLOBALS['TL_CONFIG']['rating_textposition'] = 'after';
$GLOBALS['TL_CONFIG']['rating_listsize']     = 10;
$GLOBALS['TL_CONFIG']['rating_template']     = 'rateit_default';
$GLOBALS['TL_CONFIG']['rating_description']  = '%current%/%max% %type% (%count% [Stimme|Stimmen])';
$GLOBALS['TL_CONFIG']['rating_success']      = '';
