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

use function array_flip;
use function array_keys;

final class RatingTypes
{
    /** @var RatingType[] */
    private array $ratingTypes = [];

    /** @var string[] */
    private array $activeTypesNames;

    /**
     * @param string[]     $activeTypesNames
     * @param RatingType[] $ratingTypes
     */
    public function __construct(array $activeTypesNames, iterable $ratingTypes = [])
    {
        $this->activeTypesNames = array_flip($activeTypesNames);

        foreach ($ratingTypes as $ratingType) {
            $this->register($ratingType);
        }
    }

    public function register(RatingType $ratingType): void
    {
        $this->ratingTypes[$ratingType->name()] = $ratingType;
    }

    public function has(string $type): bool
    {
        if (! isset($this->activeTypesNames[$type])) {
            return false;
        }

        return isset($this->ratingTypes[$type]);
    }

    public function sourceInformation(string $type, int $sourceId): SourceInformation|null
    {
        if (! $this->has($type)) {
            return null;
        }

        return $this->ratingTypes[$type]->sourceInformation($sourceId);
    }

    /** @return list<string> */
    public function activeTypeNames(): array
    {
        return array_keys($this->ratingTypes);
    }
}
