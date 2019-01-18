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

namespace Hofff\Contao\RateIt\Frontend;

use Contao\Hybrid;
use Contao\Model;
use Contao\Model\Collection;

/**
 * Class RateItFrontend
 */
class RateItFrontend extends Hybrid
{

    /**
     * Primary key
     * @var string
     */
    protected $strPk = 'id';

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'rateit_default';

    /**
     * Anzahl der Herzen/Sterne
     * @var int
     */
    protected $intStars = 5;

    /**
     * Textposition
     * @var string
     */
    protected $strTextPosition = 'after';

    /**
     * Initialize the controller
     */
    public function __construct($objElement = array())
    {
        if (! empty($objElement)) {
            if ($objElement instanceof Model) {
                $this->strTable = $objElement->getTable();
            } elseif ($objElement instanceof Collection) {
                $this->strTable = $objElement->current()->getTable();
            }

            $this->strKey = $this->strPk;
        }

        $stars = intval($GLOBALS['TL_CONFIG']['rating_count']);
        if ($stars > 0) {
            $this->intStars = $stars;
        }
        parent::__construct($objElement);
    }

    /**
     * Display a wildcard in the back end
     * @return string
     */
    public function generate()
    {
        return parent::generate();
        $this->loadLanguageFile('default');
        $stars         = intval($GLOBALS['TL_CONFIG']['rating_count']);
        if ($stars > 0) {
            $this->intStars = $stars;
        }
        $this->strTemplate     = $GLOBALS['TL_CONFIG']['rating_template'];
        $this->strTextPosition = $GLOBALS['TL_CONFIG']['rating_textposition'];
    }


    /**
     * Generate the module/content element
     */
    protected function compile()
    {
    }

    public function getStarMessage($rating)
    {
        $this->loadLanguageFile('default');
        $stars = $this->percentToStars($rating['rating']);
        preg_match('/^.*\[(.+)\|(.+)\].*$/i', $GLOBALS['TL_CONFIG']['rating_description'], $labels);
        if (! is_array($labels) && (! count($labels) == 2 || ! count($labels) == 3)) {
            $label       = ($rating['totalRatings'] > 1 || $rating['totalRatings'] == 0) || ! $rating ? $GLOBALS['TL_LANG']['rateit']['rating_label'][1] : $GLOBALS['TL_LANG']['rateit']['rating_label'][0];
            $description = '%current%/%max% %type% (%count% [' . $GLOBALS['TL_LANG']['tl_rateit']['vote'][0] . '|' . $GLOBALS['TL_LANG']['tl_rateit']['vote'][1] . '])';
        } else {
            $label       = count($labels) == 2 ? $labels[1] : ($rating['totalRatings'] > 1 || $rating['totalRatings'] == 0) || ! $rating ? $labels[2] : $labels[1];
            $description = $GLOBALS['TL_CONFIG']['rating_description'];
        }
        $actValue = $rating === false ? 0 : $rating['totalRatings'];
        $type     = $GLOBALS['TL_LANG']['rateit']['stars'];
// 		return str_replace('.', ',', $stars)."/$this->intStars ".$type." ($actValue $label)";
        $description = str_replace('%current%', str_replace('.', ',', $stars), $description);
        $description = str_replace('%max%', $this->intStars, $description);
        $description = str_replace('%type%', $type, $description);
        $description = str_replace('%count%', $actValue, $description);
        $description = preg_replace('/^(.*)(\[.*\])(.*)$/i', "\\1$label\\3", $description);
        return $description;
    }

    public function loadRating($rkey, $typ)
    {
        $SQL_GET_RATINGS = "SELECT i.rkey AS rkey,
			i.title AS title,
			IFNULL(AVG(r.rating),0) AS rating,
			COUNT( r.rating ) AS totalRatings
			FROM tl_rateit_items i
			LEFT OUTER JOIN tl_rateit_ratings r
			ON ( i.id = r.pid ) WHERE i.rkey = ? and typ=? and active='1'
			GROUP BY i.rkey, i.title;";
        $result          = $this->Database->prepare($SQL_GET_RATINGS)
            ->execute($rkey, $typ)
            ->fetchAssoc();
        return $result;
    }

    protected function percentToStars($percent)
    {
        $modifier = 100 / $this->intStars;
        return round($percent / $modifier, 1);
    }
}
