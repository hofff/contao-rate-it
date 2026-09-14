<?php

declare(strict_types=1);

namespace Hofff\Contao\RateIt\Test\Rating;

use Contao\FrontendUser;
use Hofff\Contao\RateIt\Rating\DetermineCurrentUserId;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final class DetermineCurrentUserIdTest extends TestCase
{
    public function testReturnsNullWhenNoTokenIsPresent(): void
    {
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn(null);

        $determineCurrentUserId = new DetermineCurrentUserId($tokenStorage);

        self::assertNull($determineCurrentUserId());
    }

    public function testReturnsNullWhenUserIsNotAFrontendUser(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn(null);

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn($token);

        $determineCurrentUserId = new DetermineCurrentUserId($tokenStorage);

        self::assertNull($determineCurrentUserId());
    }

    public function testReturnsFrontendUserId(): void
    {
        $user = $this->createMock(FrontendUser::class);
        $user->method('__get')->with('id')->willReturn(42);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn($token);

        $determineCurrentUserId = new DetermineCurrentUserId($tokenStorage);

        self::assertSame(42, $determineCurrentUserId());
    }
}
