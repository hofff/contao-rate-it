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

use Contao\ArticleModel;
use Contao\BackendTemplate;
use Contao\Config;
use Contao\Environment;
use Contao\FrontendTemplate;
use Contao\Input;
use Contao\NewsModel;
use Contao\PageModel;
use Contao\StringUtil;

/**
 * Class RateItTopRatingsModule
 */
class RateItTopRatingsModule extends RateItFrontend
{
    private static $arrUrlCache = array();

    /**
     * Initialize the controller
     */
    public function __construct($objElement)
    {
        parent::__construct($objElement);

        $this->strKey = "rateit_top_ratings";
    }

    /**
     * Display a wildcard in the back end
     * @return string
     */
    public function generate()
    {
        if (TL_MODE === 'BE') {
            $objTemplate = new BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### Rate IT Best/Most Ratings ###';
            $objTemplate->title    = $this->name;
            $objTemplate->id       = $this->id;
            $objTemplate->link     = $this->name;
            $objTemplate->href     = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        $this->strTemplate = $this->rateit_template;

        $this->arrTypes = StringUtil::deserialize($this->rateit_types);

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

        $this->import("\\Database", "Database");
        $arrResult = $this->Database->prepare("SELECT i.id AS item_id,
				i.rkey AS rkey,
				i.title AS title,
				i.typ AS typ,
				i.createdat AS createdat,
				i.active AS active,
				IFNULL(AVG(r.rating),0) AS best,
				COUNT( r.rating ) AS most
			FROM tl_rateit_items i
				LEFT OUTER JOIN tl_rateit_ratings r
					ON (i.id = r.pid)
			WHERE
				typ IN ('" . implode("', '", $this->arrTypes) . "')
			GROUP BY rkey, title, item_id, typ, createdat, active
			ORDER BY " . $this->rateit_toptype . " DESC")
            ->limit($this->rateit_count)
            ->execute()
            ->fetchAllAssoc();

        $objReturn = array();
        foreach ($arrResult as $result) {
            $return        = new \stdClass();
            $return->title = $result['title'];
            $return->typ   = $result['typ'];

            // ID ermitteln
            $stars                 = $this->percentToStars($result['best']);
            $return->rateItID      = 'rateItRating-' . $result['rkey'] . '-' . $result['typ'] . '-' .
                $stars . '_' . intval($GLOBALS['TL_CONFIG']['rating_count']);
            $return->descriptionId = 'rateItRating-' . $result['rkey'] . '-description';

            $return->rateit_class = 'rateItRating';

            $return->url = $this->getUrl($result);

            // Beschriftung ermitteln
            $rating                 = array();
            $rating['totalRatings'] = $result['most'];
            $rating['rating']       = $result['best'];
            $return->description    = $this->getStarMessage($rating);

            $return->rating = $result['best'];
            $return->count  = $result['most'];
            $return->rel    = 'not-rateable';
            $objReturn[]    = $return;
        }

        $this->Template->arrRatings = $objReturn;
    }

    private function getUrl($rating)
    {
        if ($rating['typ'] === 'page') {
            return PageModel::findById($rating['rkey'])->getAbsoluteUrl();
        }
        if ($rating['typ'] === 'article') {
            $objArticle = ArticleModel::findPublishedById($rating['rkey']);
            if (! is_null($objArticle)) {
                return PageModel::findById($objArticle->pid)->getAbsoluteUrl() . '#' . $objArticle->alias;
            }
        }
        if ($rating['typ'] === 'news') {
            $objNews    = NewsModel::findById($rating['rkey']);
            $objArticle = NewsModel::findPublishedByPid($objNews->pid);

            // Internal link
            if ($objArticle->source != 'external') {
                return $this->generateNewsUrl($objNews);
            }

            // Encode e-mail addresses
            if (substr($objArticle->url, 0, 7) == 'mailto:') {
                $strArticleUrl = StringUtil::encodeEmail($objArticle->url);
            } // Ampersand URIs
            else {
                $strArticleUrl = ampersand($objArticle->url);
            }

            /** @var \PageModel $objPage */
            global $objPage;

            // External link
            return $strArticleUrl;
        }
        return false;
    }

    private function generateNewsUrl($objItem)
    {
        $strCacheKey = 'id_' . $objItem->id;

        // Load the URL from cache
        if (isset(self::$arrUrlCache[$strCacheKey])) {
            return self::$arrUrlCache[$strCacheKey];
        }

        // Initialize the cache
        self::$arrUrlCache[$strCacheKey] = null;

        switch ($objItem->source) {
            // Link to an external page
            case 'external' :
                if (substr($objItem->url, 0, 7) == 'mailto:') {
                    self::$arrUrlCache[$strCacheKey] = StringUtil::encodeEmail($objItem->url);
                } else {
                    self::$arrUrlCache[$strCacheKey] = ampersand($objItem->url);
                }
                break;

            // Link to an internal page
            case 'internal' :
                if (($objTarget = $objItem->getRelated('jumpTo')) !== null) {
                    /** @var \PageModel $objTarget */
                    self::$arrUrlCache[$strCacheKey] = ampersand($objTarget->getFrontendUrl());
                }
                break;

            // Link to an article
            case 'article' :
                if (($objArticle = ArticleModel::findByPk($objItem->articleId, array(
                        'eager' => true,
                    ))) !== null && ($objPid = $objArticle->getRelated('pid')) !== null) {
                    /** @var \PageModel $objPid */
                    self::$arrUrlCache[$strCacheKey] = ampersand($objPid->getFrontendUrl('/articles/' . ((! Config::get('disableAlias') && $objArticle->alias != '') ? $objArticle->alias : $objArticle->id)));
                }
                break;
        }

        // Link to the default page
        if (self::$arrUrlCache[$strCacheKey] === null) {
            $objPage = PageModel::findWithDetails($objItem->getRelated('pid')->jumpTo);

            if ($objPage === null) {
                self::$arrUrlCache[$strCacheKey] = ampersand(Environment::get('request'), true);
            } else {
                self::$arrUrlCache[$strCacheKey] = ampersand($objPage->getFrontendUrl(((Config::get('useAutoItem') && ! Config::get('disableAlias')) ? '/' : '/items/') . ((! Config::get('disableAlias') && $objItem->alias != '') ? $objItem->alias : $objItem->id)));
            }

            // Add the current archive parameter (news archive)
            if ($blnAddArchive && Input::get('month') != '') {
                self::$arrUrlCache[$strCacheKey] .= (Config::get('disableAlias') ? '&amp;' : '?') . 'month=' . Input::get('month');
            }
        }

        return self::$arrUrlCache[$strCacheKey];
    }
}
