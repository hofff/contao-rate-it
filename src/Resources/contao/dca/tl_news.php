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

use Hofff\Contao\RateIt\EventListener\Dca\NewsDcaListener;

/**
 * Extend tl_article
 */

$GLOBALS['TL_DCA']['tl_news']['config']['onsubmit_callback'][] = [NewsDcaListener::class, 'insert'];
$GLOBALS['TL_DCA']['tl_news']['config']['ondelete_callback'][] = [NewsDcaListener::class, 'delete'];

/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_news']['palettes']['__selector__'][] = 'addRating';
$GLOBALS['TL_DCA']['tl_news']['palettes']['default']        = $GLOBALS['TL_DCA']['tl_news']['palettes']['default'] . ';{rating_legend:hide},addRating';

/**
 * Add subpalettes to tl_article
 */
$GLOBALS['TL_DCA']['tl_news']['subpalettes']['addRating'] = 'rateit_position';

// Fields
$GLOBALS['TL_DCA']['tl_news']['fields']['addRating'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_news']['addRating'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'sql'       => "char(1) NOT NULL default ''",
    'eval'      => ['tl_class' => 'w50 m12', 'submitOnChange' => true],
];

$GLOBALS['TL_DCA']['tl_news']['fields']['rateit_position'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_news']['rateit_position'],
    'default'   => 'before',
    'exclude'   => true,
    'inputType' => 'select',
    'options'   => ['after', 'before'],
    'reference' => &$GLOBALS['TL_LANG']['tl_news'],
    'sql'       => "varchar(6) NOT NULL default ''",
    'eval'      => ['mandatory' => true, 'tl_class' => 'w50'],
];
