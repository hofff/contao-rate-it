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

final class SourceInformation
{
    /** @var string */
    private $title;

    /** @var bool */
    private $active;

    /** @var string */
    private $parentStatus;

    public function __construct(string $title, bool $active, string $parentStatus)
    {
        $this->title        = $title;
        $this->active       = $active;
        $this->parentStatus = $parentStatus;
    }

    public function active(): bool
    {
        return $this->active;
    }

    public function parentStatus(): string
    {
        return $this->parentStatus;
    }

    public function title(): string
    {
        return $this->title;
    }
}
