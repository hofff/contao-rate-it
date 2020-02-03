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

use Contao\ModuleModel;

final class ModuleRatingType extends BaseParentSourceRatingType
{
    public function name() : string
    {
        return 'module';
    }

    protected function determineParentPublishedState(array $record) : bool
    {
        return true;
    }

    protected function tableName() : string
    {
        return ModuleModel::getTable();
    }

    protected function labelKey() : string
    {
        return 'name';
    }

    protected function activeKey() : string
    {
        return 'rateit_active';
    }
}
