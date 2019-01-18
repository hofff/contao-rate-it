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

use Hofff\Contao\RateIt\EventListener\Dca\ContentBaseDcaListener;

$GLOBALS['TL_DCA']['tl_content']['config']['onsubmit_callback'][] = [ContentBaseDcaListener::class, 'insert'];
$GLOBALS['TL_DCA']['tl_content']['config']['ondelete_callback'][] = [ContentBaseDcaListener::class, 'delete'];

/**
 * palettes
 */
$GLOBALS['TL_DCA']['tl_content']['palettes']['rateit']  = '{type_legend},type,rateit_title;{rateit_legend},rateit_active;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_content']['palettes']['gallery'] = $GLOBALS['TL_DCA']['tl_content']['palettes']['gallery'] . ';{rateit_legend},rateit_active';

/**
 * fields
 */
$GLOBALS['TL_DCA']['tl_content']['fields']['rateit_title'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_content']['rateit_title'],
    'default'   => '',
    'exclude'   => true,
    'inputType' => 'text',
    'sql'       => "varchar(255) NOT NULL default ''",
    'eval'      => ['mandatory' => true, 'maxlength' => 255],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['rateit_active'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_content']['rateit_active'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'sql'       => "char(1) NOT NULL default ''",
    'eval'      => ['tl_class' => 'w50'],
];
