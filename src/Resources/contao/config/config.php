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

?>
