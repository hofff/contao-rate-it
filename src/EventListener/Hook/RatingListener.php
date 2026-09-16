<?php

/**
 * This file is part of hofff/contao-rate-it.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author     David Molineus <david@hofff.com>
 * @copyright  2019 hofff.com.
 * @license    https://github.com/hofff/contao-rate-it/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace Hofff\Contao\RateIt\EventListener\Hook;

use Contao\FrontendTemplate;
use Hofff\Contao\RateIt\Rating\DetermineCurrentUserId;
use Hofff\Contao\RateIt\Rating\DetermineRatingTemplateName;
use Hofff\Contao\RateIt\Rating\RatingService;

abstract class RatingListener
{
    /** @SuppressWarnings(PHPMD.LongVariable) */
    public function __construct(
        protected readonly RatingService $ratingService,
        private readonly DetermineCurrentUserId $determineCurrentUserId,
        private readonly DetermineRatingTemplateName $determineRatingTemplateName,
    ) {
    }

    protected function getRating(string $type, int $ratingTypeId): array|null
    {
        return $this->ratingService->getRating($type, $ratingTypeId, ($this->determineCurrentUserId)());
    }

    protected function getRatingTemplate(): string
    {
        return ($this->determineRatingTemplateName)();
    }

    /** @param array<string, mixed> $data */
    protected function render(array $data): string
    {
        $template = new FrontendTemplate($this->getRatingTemplate());
        $template->setData($data);

        return $template->parse();
    }
}
