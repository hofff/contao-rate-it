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
use Contao\ModuleModel;

final class ModuleRatingType extends BaseParentSourceRatingType
{
    public function name() : string
    {
        return 'module';
    }

    protected function determineParentPublishedState(int $sourceId) : ?bool
    {
        $model = $this->loadModel($sourceId);
        if ($model) {
            return true;
        }

        return null;
    }

    protected function loadModel(int $sourceId) : ?Model
    {
        return ModuleModel::findByPk($sourceId);
    }

    protected function labelKey() : string
    {
        return 'name';
    }
}
