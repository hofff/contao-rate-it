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

use Hofff\Contao\RateIt\RateItBackend;

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['generatePage'][]      = [\Hofff\Contao\RateIt\RateItPage::class, 'generatePage'];
$GLOBALS['TL_HOOKS']['parseArticles'][]     = [\Hofff\Contao\RateIt\RateItNews::class, 'parseArticle'];
$GLOBALS['TL_HOOKS']['getContentElement'][] = [\Hofff\Contao\RateIt\RateItFaq::class, 'getContentElementRateIt'];
$GLOBALS['TL_HOOKS']['parseTemplate'][]     = [\Hofff\Contao\RateIt\RateItArticle::class, 'parseTemplateRateIt'];

/**
 * Back end modules
 */
array_insert(
    $GLOBALS['BE_MOD']['content'],
    -1,
    [
        'rateit' => [
            'callback'   => \Hofff\Contao\RateIt\RateItBackendModule::class,
            'icon'       => RateItBackend::image('icon'),
            'stylesheet' => RateItBackend::css('backend'),
            'javascript' => RateItBackend::js('RateItBackend'),
        ],
    ]);

/**
 * frontend moduls
 */
$GLOBALS['FE_MOD']['application']['rateit']             = \Hofff\Contao\RateIt\RateItModule::class;
$GLOBALS['FE_MOD']['application']['rateit_top_ratings'] = \Hofff\Contao\RateIt\RateItTopRatingsModule::class;

/**
 * content elements
 */
$GLOBALS['TL_CTE']['includes']['rateit'] = \Hofff\Contao\RateIt\RateItCE::class;
