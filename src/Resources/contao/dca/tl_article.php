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

use Hofff\Contao\RateIt\DcaHelper;

/**
 * Extend tl_article
 */

$GLOBALS['TL_DCA']['tl_article']['config']['onsubmit_callback'][] = array('tl_article_rating', 'insert');
$GLOBALS['TL_DCA']['tl_article']['config']['ondelete_callback'][] = array('tl_article_rating', 'delete');

/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_article']['palettes']['__selector__'][] = 'addRating';
$GLOBALS['TL_DCA']['tl_article']['palettes']['default']        = $GLOBALS['TL_DCA']['tl_article']['palettes']['default'] . ';{rateit_legend:hide},addRating';

/**
 * Add subpalettes to tl_article
 */
$GLOBALS['TL_DCA']['tl_article']['subpalettes']['addRating'] = 'rateit_position,rateit_template';

// Fields
$GLOBALS['TL_DCA']['tl_article']['fields']['addRating'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_article']['addRating'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'sql'       => "char(1) NOT NULL default ''",
    'eval'      => array('tl_class' => 'w50 m12', 'submitOnChange' => true),
);

$GLOBALS['TL_DCA']['tl_article']['fields']['rateit_position'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_article']['rateit_position'],
    'default'   => 'before',
    'exclude'   => true,
    'inputType' => 'select',
    'options'   => array('after', 'before'),
    'reference' => &$GLOBALS['TL_LANG']['tl_article'],
    'sql'       => "varchar(6) NOT NULL default ''",
    'eval'      => array('mandatory' => true, 'tl_class' => 'w50'),
);

class tl_article_rating extends DcaHelper
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function insert(\DC_Table $dc)
    {
        return $this->insertOrUpdateRatingKey($dc, 'article', $dc->activeRecord->title);
    }

    public function delete(\DC_Table $dc)
    {
        return $this->deleteRatingKey($dc, 'article');
    }
}

?>
