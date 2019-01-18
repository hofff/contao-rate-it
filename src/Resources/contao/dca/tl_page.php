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

use Hofff\Contao\RateIt\EventListener\Dca\PageBaseDcaListener;

/**
 * Extend tl_page
 */

$GLOBALS['TL_DCA']['tl_page']['config']['onsubmit_callback'][] = [PageBaseDcaListener::class, 'insert'];
$GLOBALS['TL_DCA']['tl_page']['config']['ondelete_callback'][] = [PageBaseDcaListener::class, 'delete'];

/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_page']['palettes']['__selector__'][] = 'addRating';
foreach ($GLOBALS['TL_DCA']['tl_page']['palettes'] as $keyPalette => $valuePalette) {
    // Skip if we have a array or the palettes for subselections
    if (is_array($valuePalette) || $keyPalette == "__selector__" || $keyPalette == "root" || $keyPalette == "forward" || $keyPalette == "redirect") {
        continue;
    }

    $valuePalette .= ';{rateit_legend:hide},addRating';

    // Write new entry back in the palette
    $GLOBALS['TL_DCA']['tl_page']['palettes'][$keyPalette] = $valuePalette;
}

/**
 * Add subpalettes to tl_page
 */
$GLOBALS['TL_DCA']['tl_page']['subpalettes']['addRating'] = 'rateit_position';

// Fields
$GLOBALS['TL_DCA']['tl_page']['fields']['addRating'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_page']['addRating'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'sql'       => "char(1) NOT NULL default ''",
    'eval'      => array('tl_class' => 'w50 m12', 'submitOnChange' => true),
);

$GLOBALS['TL_DCA']['tl_page']['fields']['rateit_position'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_page']['rateit_position'],
    'default'   => 'before',
    'exclude'   => true,
    'inputType' => 'select',
    'options'   => array('after', 'before'),
    'reference' => &$GLOBALS['TL_LANG']['tl_page'],
    'sql'       => "varchar(6) NOT NULL default ''",
    'eval'      => array('mandatory' => true, 'tl_class' => 'w50'),
);
