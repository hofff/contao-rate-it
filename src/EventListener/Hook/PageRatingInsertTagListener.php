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

namespace Hofff\Contao\RateIt\EventListener\Hook;

final class PageRatingInsertTagListener extends RatingListener
{
    /** @return string|false */
    public function onReplaceInsertTags(string $tag)
    {
        if (strpos($tag, 'rateit_page_rating') !== 0) {
            return false;
        }

        if (!isset($GLOBALS['objPage']) || !$GLOBALS['objPage']->addRating) {
            return '';
        }

        return $this->render((array) $this->getRating('page', (int) $GLOBALS['objPage']->id));
    }
}
