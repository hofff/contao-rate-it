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

Use Hofff\Contao\RateIt\DcaHelper;

/**
 * palettes
 */
$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{rateit_legend:hide},rating_type,rating_count,rating_textposition,rating_listsize,rating_allow_duplicate_ratings,rating_allow_duplicate_ratings_for_members,rating_template,rating_description';

/**
 * fields
 */
$GLOBALS['TL_DCA']['tl_settings']['fields']['rating_type'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_settings']['rating_type'],
    'default'   => 'hearts',
    'exclude'   => true,
    'inputType' => 'select',
    'options'   => array('hearts', 'stars'),
    'reference' => &$GLOBALS['TL_LANG']['tl_settings'],
    'eval'      => array('mandatory' => true, 'tl_class' => 'w50'),
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['rating_count'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_settings']['rating_count'],
    'default'   => '5',
    'exclude'   => true,
    'inputType' => 'select',
    'options'   => array('1', '5', '10'),
    'reference' => &$GLOBALS['TL_LANG']['tl_settings'],
    'eval'      => array('mandatory' => true, 'tl_class' => 'w50'),
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['rating_textposition'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_settings']['rating_textposition'],
    'default'   => 'after',
    'exclude'   => true,
    'inputType' => 'select',
    'options'   => array('after', 'before'),
    'reference' => &$GLOBALS['TL_LANG']['tl_settings'],
    'eval'      => array('mandatory' => true, 'tl_class' => 'w50'),
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['rating_listsize'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_settings']['rating_listsize'],
    'exclude'   => true,
    'default'   => 10,
    'inputType' => 'text',
    'eval'      => array('mandatory' => false, 'maxlength' => 4, 'tl_class' => 'w50'),
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['rating_allow_duplicate_ratings'] = array
(
    'exclude'   => true,
    'label'     => &$GLOBALS['TL_LANG']['tl_settings']['allow_duplicate_ratings'],
    'inputType' => 'checkbox',
    'eval'      => array('tl_class' => 'w50 m12'),
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['rating_allow_duplicate_ratings_for_members'] = array
(
    'exclude'   => true,
    'label'     => &$GLOBALS['TL_LANG']['tl_settings']['allow_duplicate_ratings_for_members'],
    'inputType' => 'checkbox',
    'eval'      => array('tl_class' => 'w50 m12'),
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['rating_template'] = array
(
    'label'            => &$GLOBALS['TL_LANG']['tl_settings']['rating_template'],
    'default'          => 'rateit_default',
    'exclude'          => true,
    'inputType'        => 'select',
    'options_callback' => array('tl_settings_rateit', 'getRateItTemplates'),
    'eval'             => array('mandatory' => true, 'tl_class' => 'w50'),
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['rating_description'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_settings']['rating_description'],
    'exclude'   => true,
    'default'   => '%current%/%max% %type% (%count% [Stimme|Stimmen])',
    'inputType' => 'text',
    'eval'      => array('mandatory' => true, 'allowHtml' => true, 'tl_class' => 'w50'),
);

class tl_settings_rateit extends DcaHelper
{
}

?>
