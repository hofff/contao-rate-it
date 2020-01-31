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

use Contao\Model;

abstract class BaseParentSourceRatingType extends BaseRatingType
{
    protected function determineParentPublishedState(int $sourceId) : ?bool
    {
        $model = $this->loadModel($sourceId);
        if ($model) {
            return (bool) $model->published;
        }

        return null;
    }

    public function determineActiveState(int $sourceId) : bool
    {
        $model = $this->loadModel($sourceId);
        if ($model) {
            return (bool) $model->addRating;
        }

        return false;
    }

    public function generateTitle(int $sourceId) : string
    {
        $model = $this->loadModel($sourceId);
        if ($model) {
            return (string) $model->{$this->labelKey()};
        }

        return '';
    }

    abstract protected function loadModel(int $sourceId) : ?Model;

    abstract protected function labelKey() : string;
}
