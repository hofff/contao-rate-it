<?php

declare(strict_types=1);

namespace Hofff\Contao\RateIt\Rating;

use Contao\FrontendUser;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class DetermineCurrentUserId
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function __invoke(): int|null
    {
        $token = $this->tokenStorage->getToken();
        if (! $token) {
            return null;
        }

        $user = $token->getUser();
        if ($user instanceof FrontendUser && $user->id) {
            return (int) $user->id;
        }

        return null;
    }
}
