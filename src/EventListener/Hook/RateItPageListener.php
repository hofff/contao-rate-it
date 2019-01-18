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

namespace Hofff\Contao\RateIt\EventListener\Hook;

use Contao\Frontend;
use Hofff\Contao\RateIt\RateItRating;

class RateItPageListener extends Frontend
{

    var $rateItRating;

    /**
     * Initialize the controller
     */
    public function __construct()
    {
        parent::__construct();

        $this->rateItRating = new RateItRating();
        $this->loadDataContainer('settings');
    }

    public function generatePage($objPage, $objLayout, $objPageType)
    {
        if ($objPage->addRating) {
            $actRecord = $this->Database->prepare("SELECT * FROM tl_rateit_items WHERE rkey=? and typ='page'")
                ->execute($objPage->id)
                ->fetchAssoc();

            if ($actRecord['active']) {
                $this->rateItRating->rkey = $objPage->id;
                $this->rateItRating->generate();

                $rating = $this->rateItRating->output();
                $rating .= $this->includeJs();
                $rating .= $this->includeCss();

                $objTemplate = $objPageType->Template;
                if ($objTemplate) {
                    if ($objPage->rateit_position == 'after') {
                        $objTemplate->main .= $rating;
                    } else {
                        $objTemplate->main = $rating . $objTemplate->main;
                    }
                }
            }
        }
    }

    private function includeCss()
    {
        $included    = false;
        $strHeadTags = '';
        if (is_array($GLOBALS['TL_CSS'])) {
            foreach ($GLOBALS['TL_CSS'] as $script) {
                if ($this->startsWith($script, 'bundles/hofffcontaorateit/css/rateit') === true) {
                    $included = true;
                    break;
                }
            }
        }

        if (! $included) {
            $strHeadTags = '<link rel="stylesheet" href="' . $this->addStaticUrlTo('bundles/hofffcontaorateit/css/rateit.min.css') . '">';
            switch ($GLOBALS['TL_CONFIG']['rating_type']) {
                case 'hearts' :
                    $strHeadTags .= '<link rel="stylesheet" href="' . $this->addStaticUrlTo('bundles/hofffcontaorateit/css/heart.min.css') . '">';
                    break;
                default:
                    $strHeadTags .= '<link rel="stylesheet" href="' . $this->addStaticUrlTo('bundles/hofffcontaorateit/css/star.min.css') . '">';
            }
        }
        return $strHeadTags;
    }

    private function includeJs()
    {
        $included    = false;
        $strHeadTags = '';
        if (is_array($GLOBALS['TL_JAVASCRIPT'])) {
            foreach ($GLOBALS['TL_JAVASCRIPT'] as $script) {
                if ($this->startsWith($script, 'bundles/hofffcontaorateit/js/rateit') === true) {
                    $included = true;
                    break;
                }
            }
        }

        if (! $included) {
            $strHeadTags = '<script src="' . $this->addStaticUrlTo('bundles/hofffcontaorateit/js/onReadyRateIt.js') . '"></script>' . "\n";
            $strHeadTags .= '<script src="' . $this->addStaticUrlTo('bundles/hofffcontaorateit/js/rateit.js') . '"></script>' . "\n";
        }
        return $strHeadTags;
    }

    function startsWith($haystack, $needle)
    {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
    }
}
