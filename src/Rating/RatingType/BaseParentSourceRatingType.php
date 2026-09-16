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

namespace Hofff\Contao\RateIt\Rating\RatingType;

use Override;

abstract class BaseParentSourceRatingType extends BaseRatingType
{
    /** {@inheritDoc} */
    #[Override]
    protected function generateTitle(array $record): string
    {
        return (string) $record[$this->labelKey()];
    }

    /** {@inheritDoc} */
    #[Override]
    protected function determineActiveState(array $record): bool
    {
        return (bool) $record[$this->activeKey()];
    }

    /** {@inheritDoc} */
    #[Override]
    protected function determineParentPublishedState(array $record): bool
    {
        return (bool) $record[$this->publishedKey()];
    }

    protected function labelKey(): string
    {
        return 'title';
    }

    protected function activeKey(): string
    {
        return 'addRating';
    }

    protected function publishedKey(): string
    {
        return 'published';
    }
}
