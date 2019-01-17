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

declare(strict_types=1);

use Hofff\Contao\RateIt\EventListener\Dca\SettingsDcaListener;

/**
 * palettes
 */
$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{rateit_legend:hide},rating_type,rating_count,rating_textposition,rating_listsize,rating_allow_duplicate_ratings,rating_allow_duplicate_ratings_for_members,rating_template,rating_description';

/**
 * fields
 */
$GLOBALS['TL_DCA']['tl_settings']['fields']['rating_type'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_settings']['rating_type'],
    'default'   => 'hearts',
    'exclude'   => true,
    'inputType' => 'select',
    'options'   => ['hearts', 'stars'],
    'reference' => &$GLOBALS['TL_LANG']['tl_settings'],
    'eval'      => ['mandatory' => true, 'tl_class' => 'w50'],
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['rating_count'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_settings']['rating_count'],
    'default'   => '5',
    'exclude'   => true,
    'inputType' => 'select',
    'options'   => ['1', '5', '10'],
    'reference' => &$GLOBALS['TL_LANG']['tl_settings'],
    'eval'      => ['mandatory' => true, 'tl_class' => 'w50'],
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['rating_textposition'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_settings']['rating_textposition'],
    'default'   => 'after',
    'exclude'   => true,
    'inputType' => 'select',
    'options'   => ['after', 'before'],
    'reference' => &$GLOBALS['TL_LANG']['tl_settings'],
    'eval'      => ['mandatory' => true, 'tl_class' => 'w50'],
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['rating_listsize'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_settings']['rating_listsize'],
    'exclude'   => true,
    'default'   => 10,
    'inputType' => 'text',
    'eval'      => ['mandatory' => false, 'maxlength' => 4, 'tl_class' => 'w50'],
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['rating_allow_duplicate_ratings'] = [
    'exclude'   => true,
    'label'     => &$GLOBALS['TL_LANG']['tl_settings']['allow_duplicate_ratings'],
    'inputType' => 'checkbox',
    'eval'      => ['tl_class' => 'w50 m12'],
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['rating_allow_duplicate_ratings_for_members'] = [
    'exclude'   => true,
    'label'     => &$GLOBALS['TL_LANG']['tl_settings']['allow_duplicate_ratings_for_members'],
    'inputType' => 'checkbox',
    'eval'      => ['tl_class' => 'w50 m12'],
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['rating_template'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_settings']['rating_template'],
    'default'          => 'rateit_default',
    'exclude'          => true,
    'inputType'        => 'select',
    'options_callback' => [SettingsDcaListener::class, 'getRateItTemplates'],
    'eval'             => ['mandatory' => true, 'tl_class' => 'w50'],
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['rating_description'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_settings']['rating_description'],
    'exclude'   => true,
    'default'   => '%current%/%max% %type% (%count% [Stimme|Stimmen])',
    'inputType' => 'text',
    'eval'      => ['mandatory' => true, 'allowHtml' => true, 'tl_class' => 'w50'],
];
