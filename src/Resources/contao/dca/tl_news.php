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
 * Extend tl_article
 */

$GLOBALS['TL_DCA']['tl_news']['config']['onsubmit_callback'][] = array('tl_news_rating', 'insert');
$GLOBALS['TL_DCA']['tl_news']['config']['ondelete_callback'][] = array('tl_news_rating', 'delete');

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
$GLOBALS['TL_DCA']['tl_news']['fields']['addRating'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_news']['addRating'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'sql'       => "char(1) NOT NULL default ''",
    'eval'      => array('tl_class' => 'w50 m12', 'submitOnChange' => true),
);

$GLOBALS['TL_DCA']['tl_news']['fields']['rateit_position'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_news']['rateit_position'],
    'default'   => 'before',
    'exclude'   => true,
    'inputType' => 'select',
    'options'   => array('after', 'before'),
    'reference' => &$GLOBALS['TL_LANG']['tl_news'],
    'sql'       => "varchar(6) NOT NULL default ''",
    'eval'      => array('mandatory' => true, 'tl_class' => 'w50'),
);

class tl_news_rating extends DcaHelper
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
        return $this->insertOrUpdateRatingKey($dc, 'news', $dc->activeRecord->headline);
    }

    public function delete(\DC_Table $dc)
    {
        return $this->deleteRatingKey($dc, 'news');
    }
}

?>
