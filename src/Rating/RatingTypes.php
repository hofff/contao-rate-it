<?php

/**
 * This file is part of hofff/contao-rate-it.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author     David Molineus <david@hofff.com>
 * @copyright  2019-2020 hofff.com.
 * @license    https://github.com/hofff/contao-rate-it/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace Hofff\Contao\RateIt\Rating;

final class RatingTypes
{
    /** @var RatingType[] */
    private $ratingTypes;

    /**
     * @param RatingType[] $ratingTypes
     */
    public function __construct(iterable $ratingTypes)
    {
        foreach ($ratingTypes as $ratingType) {
            $this->register($ratingType);
        }
    }

    public function register(RatingType $ratingType) : void
    {
        $this->ratingTypes[$ratingType->name()] = $ratingType;
    }

    public function determineParentStatus(string $type, int $sourceId) : string
    {
        if (!isset($this->ratingTypes[$type])) {
            return 'r';
        }

        return $this->ratingTypes[$type]->determineParentStatus($sourceId);
    }

    public function determineActiveState(string $type, int $sourceId) : bool
    {
        if (!isset($this->ratingTypes[$type])) {
            return false;
        }

        return $this->ratingTypes[$type]->determineActiveState($sourceId);
    }

    public function generateTitle(string $type, int $sourceId) : string
    {
        if (!isset($this->ratingTypes[$type])) {
            return sprintf('%s %s', $type, $sourceId);
        }

        return $this->ratingTypes[$type]->generateTitle($sourceId);
    }
}
