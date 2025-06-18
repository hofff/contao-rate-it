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

declare(strict_types=1);

namespace Hofff\Contao\RateIt\Frontend;

use Contao\ArticleModel;
use Contao\BackendTemplate;
use Contao\FrontendTemplate;
use Contao\Model;
use Contao\Model\Collection;
use Contao\NewsModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Override;
use stdClass;

use function implode;
use function intval;

/**
 * @property string|int $rateit_count
 * @property string $rateit_toptype
 * @property string $rateit_types
 * @property string $rateit_template
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class RateItTopRatingsModule extends RateItFrontend
{
    /** @var list<string> $types */
    private array $types = [];

    public function __construct(Model|Collection|null $objElement = null)
    {
        parent::__construct($objElement);

        $this->strKey = 'rateit_top_ratings';
    }

    /** Display a wildcard in the back end */
    #[Override]
    public function generate(): string
    {
        if (self::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest()) {
            $objTemplate = new BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### Rate IT Best/Most Ratings ###';
            $objTemplate->title    = $this->name;
            $objTemplate->id       = $this->id;
            $objTemplate->link     = $this->name;
            $objTemplate->href     = 'contao?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        $this->strTemplate = $this->rateit_template;

        /** @psalm-suppress PropertyTypeCoercion */
        $this->types = (array) StringUtil::deserialize($this->rateit_types, true);

        return parent::generate();
    }

    /**
     * Generate the module/content element
     */
    #[Override]
    protected function compile(): void
    {
        $this->Template = new FrontendTemplate($this->strTemplate);

        $this->Template->setData($this->arrData);

        $this->import('\\Database', 'Database');
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
				typ IN ('" . implode("', '", $this->types) . "')
			GROUP BY rkey, title, item_id, typ, createdat, active
			ORDER BY " . $this->rateit_toptype . ' DESC')
            ->limit((int) $this->rateit_count)
            ->execute()
            ->fetchAllAssoc();

        $objReturn = [];
        foreach ($arrResult as $result) {
            $return        = new stdClass();
            $return->title = $result['title'];
            $return->typ   = $result['typ'];

            // ID ermitteln
            $stars                 = (string) $this->percentToStars((float) $result['best']);
            $return->rateItID      = 'rateItRating-' . $result['rkey'] . '-' . $result['typ'] . '-' .
                $stars . '_' . intval($GLOBALS['TL_CONFIG']['rating_count']);
            $return->descriptionId = 'rateItRating-' . $result['rkey'] . '-description';

            $return->rateit_class = 'rateItRating';

            $return->url = $this->getUrl($result);

            // Beschriftung ermitteln
            $rating                 = [];
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

    /** @param array<string, mixed> $rating */
    private function getUrl(array $rating): string|null
    {
        $model = match ($rating['typ']) {
            'page' => PageModel::findById($rating['rkey']),
            'article' => ArticleModel::findPublishedById($rating['rkey']),
            'news' => NewsModel::findById($rating['rkey']),
            default => null,
        };

        if ($model === null) {
            return null;
        }

        $urlGenerator = System::getContainer()->get('contao.routing.content_url_generator');

        return $urlGenerator->generate($model);
    }
}
