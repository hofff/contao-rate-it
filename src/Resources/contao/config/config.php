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

use Hofff\Contao\RateIt\RateItBackend;

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['generatePage'][]      = array('cgoIT\rateit\RateItPage', 'generatePage');
$GLOBALS['TL_HOOKS']['parseArticles'][]     = array('cgoIT\rateit\RateItNews', 'parseArticle');
$GLOBALS['TL_HOOKS']['getContentElement'][] = array('cgoIT\rateit\RateItFaq', 'getContentElementRateIt');
$GLOBALS['TL_HOOKS']['parseTemplate'][]     = array('cgoIT\rateit\RateItArticle', 'parseTemplateRateIt');

/**
 * Back end modules
 */
array_insert($GLOBALS['BE_MOD']['content'], -1,
    array('rateit' => array(
        'callback'   => 'cgoIT\rateit\RateItBackendModule',
        'icon'       => RateItBackend::image('icon'),
        'stylesheet' => RateItBackend::css('backend'),
        'javascript' => RateItBackend::js('RateItBackend'),
    ),
    ));

/**
 * frontend moduls
 */
$GLOBALS['FE_MOD']['application']['rateit']             = 'cgoIT\rateit\RateItModule';
$GLOBALS['FE_MOD']['application']['rateit_top_ratings'] = 'cgoIT\rateit\RateItTopRatingsModule';

/**
 * content elements
 */
$GLOBALS['TL_CTE']['includes']['rateit'] = 'cgoIT\rateit\RateItCE';
