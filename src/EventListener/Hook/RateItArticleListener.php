<?php

/**
 * This file is part of hofff/contao-rate-it.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author     David Molineus <david@hofff.com>
 * @author     Carsten Götzinger <info@cgo-it.de>
 * @copyright  2019 hofff.com.
 * @copyright  2013-2018 cgo IT.
 * @license    https://github.com/hofff/contao-rate-it/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace Hofff\Contao\RateIt\EventListener\Hook;

use Contao\Config;
use Contao\Template;

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
        } else if ($template->type === 'articleList') {
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

    private function doArticleList($objTemplate) : void
    {
        if (!$objTemplate->rateit_active) {
            return;
        }

        $objTemplate->rateit_template = $this->getRatingTemplate();

        $bolTemplateFixed = false;
        $arrArticles      = array();

        foreach ($objTemplate->articles as $article) {
            $articleModel = \Contao\ArticleModel::findByPk($article['articleId']);
            if (! $articleModel) {
                continue;
            }

            $articleModel = $articleModel->row();
            if ($articleModel['addRating']) {
                if (! $bolTemplateFixed) {
                    $objTemplate->setName($objTemplate->getName() . '_rateit');
                    $bolTemplateFixed = true;
                }

                $article['rateit_position'] = Config::get('rating_textposition');
                $article['rating']          = $this->getRating('article', (int) $articleModel['id']);
            }

            $arrArticles[] = $article;
        }

        $objTemplate->articles = $arrArticles;
    }
}
