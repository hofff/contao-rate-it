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

namespace Hofff\Contao\RateIt\Rating;

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\System;
use Doctrine\DBAL\Connection;

use function str_replace;

final class RatingService
{
    private const string SQL_QUERY = <<<'SQL'

SELECT
    i.id AS id,
    i.rkey AS rkey,
    i.title AS title,
    IFNULL(AVG(r.rating),0) AS rating,
    COUNT( r.rating ) AS totalRatings
FROM
    tl_rateit_items i
LEFT OUTER JOIN
    tl_rateit_ratings r
    ON i.id = r.pid
WHERE
    i.rkey=:rkey and typ=:type and active='1'
GROUP BY i.rkey, i.id, i.title;
SQL;

    /** @var ContaoFramework */
    private $framework;

    public function __construct(
        private readonly Connection $connection,
        ContaoFramework $framework,
        private readonly IsUserAllowedToRate $isUserAllowedToRate,
    )  {
        $this->framework           = $framework;
    }

    public function getRating(string $type, int $ratingTypeId, ?int $userId) : ?array
    {
        return $this->getRatingWithMessageTemplate($type, $ratingTypeId, $GLOBALS['TL_CONFIG']['rating_description'], $userId);
    }

    public function getRatingWithSuccessMessage(string $type, int $ratingTypeId, ?int $userId) : ?array
    {
        return $this->getRatingWithMessageTemplate($type, $ratingTypeId, $GLOBALS['TL_CONFIG']['rating_success'] ?: $GLOBALS['TL_CONFIG']['rating_description'], $userId);
    }

    private function getRatingWithMessageTemplate(string $type, int $ratingTypeId, string $template, ?int $userId) : ?array
    {
        $rating = $this->loadRating($ratingTypeId, $type);
        if (! $rating) {
            return null;
        }

        $stars     = $this->percentToStars((float) $rating['rating']);
        $maxStars  = $this->maxStars();
        $sessionId = new CurrentUserId();

        return [
            'descriptionId' => sprintf('rateItRating-%s-description', $ratingTypeId),
            'description'   => $this->getStarMessageUsingTemplate($template, $rating),
            'id'            => sprintf('rateItRating-%s-%s-%s_%s', (string) $ratingTypeId, $type, $stars, $maxStars),
            'class'         => 'rateItRating',
            'itemreviewed'  => $rating['title'],
            'actRating'     => $this->percentToStars((float) $rating['rating']),
            'maxRating'     => $maxStars,
            'enabled'       => ($this->isUserAllowedToRate)((int) $rating['id'], (string) $sessionId, $userId),
            'votes'         => $rating['totalRatings'],
            'ratingId'      => $ratingTypeId,
            'ratingType'    => $type,
            'showBefore'    => $this->getConfig('rating_textposition') === 'before',
            'showAfter'     => $this->getConfig('rating_textposition') === 'after',
        ];
    }

    public function getStarMessage(array|null $rating) : string
    {
        return $this->getStarMessageUsingTemplate($GLOBALS['TL_CONFIG']['rating_description'], $rating);
    }

    public function loadRating(int $rkey, string $typ) : ?array
    {
        $statement = $this->connection->prepare(self::SQL_QUERY);
        $statement->bindValue('rkey', $rkey);
        $statement->bindValue('type', $typ);
        $result = $statement->executeQuery();

        if ($result->rowCount() === 0) {
            return null;
        }

        return (array) $result->fetchAssociative();
    }

    private function maxStars() : int
    {
        return (int) $this->getConfig('rating_count') ?: 5;
    }

    private function getConfig(string $key): mixed
    {
        $this->framework->initialize();

        return $this->framework->getAdapter(Config::class)->get($key);
    }

    // TODO: Rework
    private function getStarMessageUsingTemplate(string $template, ?array $rating) : string
    {
        $this->framework->initialize();
        $this->framework->getAdapter(System::class)->loadLanguageFile('default');

        preg_match('/^.*\[(.+)\|(.+)\].*$/i', $template, $labels);
        if (count($labels) !== 2 && count($labels) !== 3) {
            if ($rating === null || ($rating['totalRatings'] > 1 || $rating['totalRatings'] == 0)) {
                $label = $GLOBALS['TL_LANG']['rateit']['rating_label'][1];
            } else {
                $label = $GLOBALS['TL_LANG']['rateit']['rating_label'][0];
            }

            $description = '%current%/%max% %type% (%count% [' . $GLOBALS['TL_LANG']['tl_rateit']['vote'][0] . '|' . $GLOBALS['TL_LANG']['tl_rateit']['vote'][1] . '])';
        } else {
            $label       = count($labels) == 2
                ? $labels[1]
                : (! $rating || ($rating['totalRatings'] > 1 || $rating['totalRatings'] == 0) ? $labels[2] : $labels[1]);
            $description = $template;
        }
        $actValue = $rating === null ? 0 : $rating['totalRatings'];
        $stars = $rating ? $this->percentToStars((float) $rating['rating']) : 0;
        $description = strtr($description, [
            '%current%' => str_replace('.', ',', (string) $stars),
            '%max%'     => (string) $this->maxStars(),
            '%type%'    => $GLOBALS['TL_LANG']['rateit']['stars'],
            '%count%'   => (string) $actValue,
        ]);

        return (string) preg_replace('/^(.*)(\[.*\])(.*)$/i', "\\1$label\\3", $description);
    }

    private function percentToStars(float $rating) : float
    {
        $modifier = (float) (100 / $this->maxStars());
        return round($rating / $modifier, 1);
    }
}
