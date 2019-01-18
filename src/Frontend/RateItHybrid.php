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

use Contao\BackendTemplate;
use Contao\FrontendTemplate;

/**
 * Class RateItHybrid
 */
abstract class RateItHybrid extends RateItFrontend
{
    //protected $intStars = 5;

    /**
     * Initialize the controller
     */
    public function __construct($objElement)
    {
        parent::__construct($objElement);
    }

    /**
     * Display a wildcard in the back end
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE') {
            $objTemplate = new BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### Rate IT ###';
            $objTemplate->title    = $this->rateit_title;
            $objTemplate->id       = $this->id;
            $objTemplate->link     = $this->name;
            $objTemplate->href     = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        $this->strTemplate = $GLOBALS['TL_CONFIG']['rating_template'];
        $this->strTextPosition = $GLOBALS['TL_CONFIG']['rating_textposition'];

        $GLOBALS['TL_JAVASCRIPT']['rateit'] = 'bundles/hofffcontaorateit/js/script.js|static';

        return parent::generate();
    }

    /**
     * Generate the module/content element
     */
    protected function compile()
    {
        $this->Template = new FrontendTemplate($this->strTemplate);

        $this->Template->setData($this->arrData);

        $rating   = $this->loadRating($this->getParent()->id, $this->getType());
        $ratingId = $this->getParent()->id;
        $stars    = ! $rating ? 0 : $this->percentToStars($rating['rating']);
        $percent  = round($rating['rating'], 0) . "%";

        $this->Template->descriptionId = 'rateItRating-' . $ratingId . '-description';
        $this->Template->description   = $this->getStarMessage($rating);
        $this->Template->id            = 'rateItRating-' . $ratingId . '-' . $this->getType() . '-' . $stars . '_' . $this->intStars;
        $this->Template->rateit_class  = 'rateItRating';
        $this->Template->itemreviewed  = $rating['title'];
        $this->Template->actRating     = $this->percentToStars($rating['rating']);
        $this->Template->maxRating     = $this->intStars;
        $this->Template->votes         = $rating['totalRatings'];

        if ($this->strTextPosition == "before") {
            $this->Template->showBefore = true;
        } else if ($this->strTextPosition == "after") {
            $this->Template->showAfter = true;
        }

        return parent::compile();
    }
}
