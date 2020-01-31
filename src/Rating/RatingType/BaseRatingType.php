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

use Hofff\Contao\RateIt\Rating\RatingType;

abstract class BaseRatingType implements RatingType
{
    public function determineParentStatus(int $sourceId) : string
    {
        $published = $this->determineParentPublishedState($sourceId);

        switch ($published) {
            case true:
                return 'a';

            case false:
                return 'i';

            case null:
            default:
                return 'r';
        }
    }

    abstract protected function determineParentPublishedState(int $sourceId) : ?bool;
}
