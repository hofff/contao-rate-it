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

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\FrontendTemplate;
use Contao\FrontendUser;
use Hofff\Contao\RateIt\Rating\RatingService;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

abstract class RatingListener
{
    public function __construct(
        protected readonly RatingService $ratingService,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ContaoFramework $framework
    ) {
    }

    protected function getRating(string $type, int $ratingTypeId): ?array
    {
        return $this->ratingService->getRating($type, $ratingTypeId, $this->getUserId());
    }

    protected function getRatingTemplate(): string
    {
        return $this->framework->getAdapter(Config::class)->get('rating_template') ?: 'ratit_default';
    }

    protected function render(array $data): string
    {
        $template = new FrontendTemplate($this->getRatingTemplate());
        $template->setData($data);

        return $template->parse();
    }

    private function getUserId(): ?int
    {
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return null;
        }

        $user = $token->getUser();
        if ($user instanceof FrontendUser && $user->id) {
            return $user->id;
        }

        return null;
    }
}
