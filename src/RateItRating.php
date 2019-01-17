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

namespace Hofff\Contao\RateIt;

class RateItRating extends RateItFrontend
{

    /**
     * RatingKey
     * @var int
     */
    public $rkey = 0;

    public $ratingType = 'page';

    /**
     * Initialize the controller
     */
    public function __construct($objElement = array())
    {
        parent::__construct($objElement);
    }

    /**
     * Display a wildcard in the back end
     * @return string
     */
    public function generate()
    {
        parent::generate();
    }

    /**
     * Compile
     */
    protected function compile()
    {
        $this->loadLanguageFile('default');

        $this->Template = new \FrontendTemplate($this->strTemplate);
        $this->Template->setData($this->arrData);

        $rating   = $this->loadRating($this->rkey, $this->ratingType);
        $ratingId = $this->rkey;
        $stars    = ! $rating ? 0 : $this->percentToStars($rating['rating']);
        $percent  = round($rating['rating'], 0) . "%";

        $this->Template->descriptionId = 'rateItRating-' . $ratingId . '-description';
        $this->Template->description   = $this->getStarMessage($rating);
        $this->Template->id            = 'rateItRating-' . $ratingId . '-' . $this->ratingType . '-' . $stars . '_' . $this->intStars;
        $this->Template->class         = 'rateItRating';
        $this->Template->itemreviewed  = $rating['title'];
        $this->Template->actRating     = $this->percentToStars($rating['rating']);
        $this->Template->maxRating     = $this->intStars;
        $this->Template->votes         = $rating[totalRatings];

        if ($this->strTextPosition == "before") {
            $this->Template->showBefore = true;
        } else if ($this->strTextPosition == "after") {
            $this->Template->showAfter = true;
        }

        return $this->Template->parse();
    }

    public function output()
    {
        return $this->compile();
    }
}
