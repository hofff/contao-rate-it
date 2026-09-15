<?php

/**
 * This file is part of hofff/contao-rate-it.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author     David Molineus <david@hofff.com>
 * @author     Carsten Götzinger <info@cgo-it.de>
 * @copyright  2019 hofff.com.
 * @copyright  2013-2018 cgo IT.
 * @license    https://github.com/hofff/contao-rate-it/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

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
            /** @psalm-suppress RedundantCastGivenDocblockType */
            return (int) $user->id;
        }

        return null;
    }
}
