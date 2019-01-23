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
use Contao\Template;
use Hofff\Contao\RateIt\Frontend\RateItFrontend;
use function strpos;

class RateItArticleListener extends RatingListener
{
    public function onParseTemplate(Template $template) : void
    {
        // TODO: Check if other template names are required, maybe
//        if (strpos($objTemplate->getName(), 'mod_article') !== 0) {
//            return;
//        }

        if ($template->type === 'article') {
            $this->doArticle($template);
        } else if ($template->type == 'articleList') {
            $this->doArticleList($template);
        }
    }

    private function doArticle(Template $template) : void
    {
        if (! $template->addRating) {
            return;
        }

        $template->rateit_template = $this->getRatingTemplate();
        $template->rating          = $this->getRating('article', (int) $template->id);
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
                }

                $arrArticles[] = $article;
            }
            $objTemplate->articles = $arrArticles;
        }
        return $objTemplate;
    }
}
