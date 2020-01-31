<?php

/**
 * This file is part of hofff/contao-rate-it.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author     David Molineus <david@hofff.com>
 * @copyright  2019-2020 hofff.com.
 * @license    https://github.com/hofff/contao-rate-it/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

use Hofff\Contao\RateIt\EventListener\Dca\NewsArchiveDcaListener;

/**
 * Extend tl_article
 */

$GLOBALS['TL_DCA']['tl_news_archive']['config']['onload_callback'][] = [NewsArchiveDcaListener::class, 'onLoad'];

/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_news_archive']['palettes']['__selector__'][] = 'addRating';

/**
 * Add subpalettes to tl_article
 */
$GLOBALS['TL_DCA']['tl_news_archive']['palettes']['__selector__'][]       = 'addCommentsRating';
$GLOBALS['TL_DCA']['tl_news_archive']['subpalettes']['addCommentsRating'] = 'rateit_position_comments';

// Fields
$GLOBALS['TL_DCA']['tl_news_archive']['fields']['addCommentsRating'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_news_archive']['addCommentsRating'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'sql'       => "char(1) NOT NULL default ''",
    'eval'      => ['tl_class' => 'w50 m12', 'submitOnChange' => true],
];

$GLOBALS['TL_DCA']['tl_news_archive']['fields']['rateit_position_comments'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_news_archive']['rateit_position_comments'],
    'default'   => 'before',
    'exclude'   => true,
    'inputType' => 'select',
    'options'   => ['after', 'before'],
    'reference' => &$GLOBALS['TL_LANG']['tl_news_archive']['rateit_positions'],
    'sql'       => "varchar(6) NOT NULL default ''",
    'eval'      => ['mandatory' => true, 'tl_class' => 'w50'],
];
