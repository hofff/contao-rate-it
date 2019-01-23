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

use Contao\File;
use Contao\FilesModel;
use Contao\StringUtil;
use Hofff\Contao\RateIt\Frontend\RateItFrontend;

class RateItArticleListener extends RateItFrontend
{

    /**
     * Initialize the controller
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function parseTemplateRateIt($objTemplate)
    {
        if ($objTemplate->type == 'article') {
            $objTemplate = $this->doArticle($objTemplate);
        } else if ($objTemplate->type == 'articleList') {
            $objTemplate = $this->doArticleList($objTemplate);
        }

        return $objTemplate;
    }

    private function doArticle($objTemplate)
    {
        $arrArticle = $this->Database->prepare('SELECT * FROM tl_article WHERE ID=?')
            ->limit(1)
            ->execute($objTemplate->id)
            ->fetchAssoc();

        if ($arrArticle['addRating']) {
            $ratingId = $arrArticle['id'];
            $rating   = $this->loadRating($ratingId, 'article');
            $stars    = ! $rating ? 0 : $this->percentToStars($rating['rating']);
            $percent  = round($rating['rating'], 0) . "%";

            $objTemplate->descriptionId = 'rateItRating-' . $ratingId . '-description';
            $objTemplate->description   = $this->getStarMessage($rating);
            $objTemplate->rateItID      = 'rateItRating-' . $ratingId . '-article-' . $stars . '_' . $this->intStars;
            $objTemplate->rateit_class  = 'rateItRating';
            $objTemplate->itemreviewed  = $rating['title'];
            $objTemplate->actRating     = $this->percentToStars($rating['rating']);
            $objTemplate->maxRating     = $this->intStars;
            $objTemplate->votes         = $rating['totalRatings'];

            if ($this->strTextPosition == "before") {
                $objTemplate->showBefore = true;
            } else if ($this->strTextPosition == "after") {
                $objTemplate->showAfter = true;
            }

            if ($arrArticle['rateit_position'] == 'before') {
                $objTemplate->rateit_rating_before = true;
            } else if ($arrArticle['rateit_position'] == 'after') {
                $objTemplate->rateit_rating_after = true;
            }

            $GLOBALS['TL_JAVASCRIPT']['rateit'] = 'bundles/hofffcontaorateit/js/script.js|static';
        }

        return $objTemplate;
    }

    private function doArticleList($objTemplate)
    {
        if ($objTemplate->rateit_active) {
            $bolTemplateFixed = false;
            $arrArticles      = array();
            foreach ($objTemplate->articles as $article) {
                $arrArticle = $this->Database->prepare('SELECT * FROM tl_article WHERE ID=?')
                    ->limit(1)
                    ->execute($article['articleId'])
                    ->fetchAssoc();

                if ($arrArticle['addRating']) {
                    if (! $bolTemplateFixed) {
                        $objTemplate->setName($objTemplate->getName() . '_rateit');
                        $bolTemplateFixed = true;
                    }

                    $ratingId = $arrArticle['id'];
                    $rating   = $this->loadRating($ratingId, 'article');
                    $stars    = ! $rating ? 0 : $this->percentToStars($rating['rating']);
                    $percent  = round($rating['rating'], 0) . "%";

                    $article['descriptionId'] = 'rateItRating-' . $ratingId . '-description';
                    $article['description']   = $this->getStarMessage($rating);
                    $article['rateItID']      = 'rateItRating-' . $ratingId . '-article-' . $stars . '_' . $this->intStars;
                    $article['rateit_class']  = 'rateItRating';
                    $article['itemreviewed']  = $rating['title'];
                    $article['actRating']     = $this->percentToStars($rating['rating']);
                    $article['maxRating']     = $this->intStars;
                    $article['votes']         = $rating['totalRatings'];

                    if ($this->strTextPosition == "before") {
                        $article['showBefore'] = true;
                    } else if ($this->strTextPosition == "after") {
                        $article['showAfter'] = true;
                    }

                    if ($arrArticle['rateit_position'] == 'before') {
                        $article['rateit_rating_before'] = true;
                    } else if ($arrArticle['rateit_position'] == 'after') {
                        $article['rateit_rating_after'] = true;
                    }

                    $GLOBALS['TL_JAVASCRIPT']['rateit'] = 'bundles/hofffcontaorateit/js/script.js|static';
                }

                $arrArticles[] = $article;
            }
            $objTemplate->articles = $arrArticles;
        }
        return $objTemplate;
    }
}
