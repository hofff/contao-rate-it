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

use Contao\ContentModel;

final class CeRatingType extends BaseParentSourceRatingType
{
    public function name() : string
    {
        return 'ce';
    }

    protected function determineParentPublishedState(array $record) : bool
    {
        return !parent::determineParentPublishedState($record);
    }

    protected function tableName() : string
    {
        return ContentModel::getTable();
    }

    protected function publishedKey() : string
    {
        return 'invisible';
    }

    protected function activeKey() : string
    {
        return 'rateit_active';
    }

    protected function labelKey() : string
    {
        return 'rateit_title';
    }
}
