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

namespace Hofff\Contao\RateIt\Controller\FrontendModule;

use Contao\ArticleModel;
use Contao\Config;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\ModuleModel;
use Contao\NewsModel;
use Contao\PageModel;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Hofff\Contao\RateIt\Rating\RatingService;
use Override;
use stdClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function array_fill;
use function count;
use function implode;
use function sprintf;

#[AsFrontendModule('rateit_top_ratings', category: 'application')]
final class RateItTopRatingsModuleController extends AbstractFrontendModuleController
{
    public function __construct(
        private readonly Connection $connection,
        private readonly RatingService $ratingService,
        private readonly ContaoFramework $framework,
        private readonly ContentUrlGenerator $urlGenerator,
    ) {
    }

    #[Override]
    public function __invoke(Request $request, ModuleModel $model, string $section, array|null $classes = null): Response
    {
        if ($this->isBackendScope($request)) {
            return $this->getBackendWildcard($model);
        }

        $template = $this->createTemplate($model, $this->determineTemplateName($model));

        $this->addDefaultDataToTemplate(
            $template,
            $model->row(),
            $section,
            $classes ?? [],
            $request->attributes->get('templateProperties', []),
        );

        $this->tagResponse($model);

        return $this->getResponse($template, $model, $request);
    }

    #[Override]
    protected function getResponse(FragmentTemplate $template, ModuleModel $model, Request $request): Response
    {
        $types = StringUtil::deserialize($model->rateit_types, true);
        $orderColumn = $model->rateit_toptype === 'most' ? 'most' : 'best';

        $template->set('ratings', $this->fetchRatings($types, $orderColumn, (int) $model->rateit_count));

        return $template->getResponse();
    }

    /**
     * @param list<string> $types
     *
     * @return list<stdClass>
     */
    private function fetchRatings(array $types, string $orderColumn, int $limit): array
    {
        if ($types === []) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($types), '?'));

        $rows = $this->connection->fetchAllAssociative(
            <<<SQL
                SELECT
                    i.id AS item_id,
                    i.rkey AS rkey,
                    i.title AS title,
                    i.typ AS typ,
                    IFNULL(AVG(r.rating), 0) AS best,
                    COUNT(r.rating) AS most
                FROM tl_rateit_items i
                LEFT OUTER JOIN tl_rateit_ratings r ON i.id = r.pid
                WHERE i.typ IN ($placeholders)
                GROUP BY i.rkey, i.title, i.item_id, i.typ, i.createdat, i.active
                ORDER BY $orderColumn DESC
                LIMIT $limit
                SQL,
            $types,
        );

        $ratings = [];

        foreach ($rows as $row) {
            $ratings[] = $this->buildRating($row);
        }

        return $ratings;
    }

    /** @param array<string, mixed> $row */
    private function buildRating(array $row): stdClass
    {
        $stars    = (string) $this->ratingService->percentToStars((float) $row['best']);
        $maxStars = $this->framework->getAdapter(Config::class)->get('rating_count');

        $rating                = new stdClass();
        $rating->title         = $row['title'];
        $rating->rateItID      = sprintf('rateItRating-%s-%s-%s_%s', $row['rkey'], $row['typ'], $stars, $maxStars);
        $rating->descriptionId = sprintf('rateItRating-%s-description', $row['rkey']);
        $rating->rateit_class  = 'rateItRating';
        $rating->rel           = 'not-rateable';
        $rating->url           = $this->resolveUrl((string) $row['typ'], (int) $row['rkey']);
        $rating->description   = $this->ratingService->getStarMessage([
            'totalRatings' => $row['most'],
            'rating' => $row['best'],
        ]);

        return $rating;
    }

    private function resolveUrl(string $type, int $rkey): string|null
    {
        $model = match ($type) {
            'page' => PageModel::findById($rkey),
            'article' => ArticleModel::findPublishedById($rkey),
            'news' => NewsModel::findById($rkey),
            default => null,
        };

        if ($model === null) {
            return null;
        }

        return $this->urlGenerator->generate($model);
    }

    private function determineTemplateName(ModuleModel $model): string
    {
        $template = (string) $model->rateit_template;

        return $template !== '' ? $template : 'mod_rateit_top_ratings';
    }
}
