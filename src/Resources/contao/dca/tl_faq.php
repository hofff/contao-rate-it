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

declare(strict_types=1);

use Hofff\Contao\RateIt\EventListener\Dca\FaqBaseDcaListener;

/**
 * Extend tl_article
 */

$GLOBALS['TL_DCA']['tl_faq']['config']['onsubmit_callback'][] = [FaqBaseDcaListener::class, 'insert'];
$GLOBALS['TL_DCA']['tl_faq']['config']['ondelete_callback'][] = [FaqBaseDcaListener::class, 'delete'];

/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_faq']['palettes']['__selector__'][] = 'addRating';
$GLOBALS['TL_DCA']['tl_faq']['palettes']['default']        = $GLOBALS['TL_DCA']['tl_faq']['palettes']['default'] . ';{rating_legend:hide},addRating';

/**
 * Add subpalettes to tl_article
 */
$GLOBALS['TL_DCA']['tl_faq']['subpalettes']['addRating'] = 'rateit_position';

// Fields
$GLOBALS['TL_DCA']['tl_faq']['fields']['addRating'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_faq']['addRating'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'sql'       => "char(1) NOT NULL default ''",
    'eval'      => ['tl_class' => 'w50 m12', 'submitOnChange' => true],
];

$GLOBALS['TL_DCA']['tl_faq']['fields']['rateit_position'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_faq']['rateit_position'],
    'default'   => 'before',
    'exclude'   => true,
    'inputType' => 'select',
    'options'   => ['after', 'before'],
    'reference' => &$GLOBALS['TL_LANG']['tl_faq'],
    'sql'       => "varchar(6) NOT NULL default ''",
    'eval'      => ['mandatory' => true, 'tl_class' => 'w50'],
];
