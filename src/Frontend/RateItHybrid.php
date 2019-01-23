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
use Hofff\Contao\RateIt\Rating\RatingService;

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
        if (TL_MODE === 'BE') {
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
    protected function compile() : void
    {
        $this->Template = new FrontendTemplate($this->strTemplate);
        $this->Template->setData($this->arrData);

        $rating = self::getContainer()->get(RatingService::class)->getRating($this->getParent()->id, $this->getType());
        foreach ((array) $rating as $key => $value) {
            $this->Template->$key = $value;
        }

        if ($this->strTextPosition === "before") {
            $this->Template->showBefore = true;
        } else if ($this->strTextPosition === "after") {
            $this->Template->showAfter = true;
        }

        parent::compile();
    }

    abstract protected function getType() : string;
}
