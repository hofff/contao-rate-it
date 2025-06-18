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

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFramework;
use Doctrine\DBAL\Connection;

final class IsUserAllowedToRate
{
    /** @var ContaoFramework */
    private $framework;

    public function __construct(private readonly Connection $connection, ContaoFramework $framework)
    {
        $this->framework  = $framework;
    }

    public function __invoke(int $ratingId, ?string $sessionId, ?int $userId): bool
    {
        $this->framework->initialize();

        if ($userId) {
            if ($this->areDuplicatesAllowedForMembers()) {
                return true;
            }

            return ! $this->hasLoggedInUserAlreadyRated($ratingId, $userId);
        }

        if (!$sessionId) {
            return true;
        }

        if ($this->areDuplicatesAllowed()) {
            return true;
        }

        return ! $this->hasAnonymousUserAlreadyRated($ratingId, $sessionId);
    }

    private function hasLoggedInUserAlreadyRated(int $ratingId, int $userId): bool
    {
        $statement = $this->connection->prepare(
            'SELECT count(*) FROM tl_rateit_ratings WHERE pid=:pid and memberid=:memberid'
        );

        $statement->bindValue('pid', $ratingId);
        $statement->bindValue('memberid', $userId);
        $result = $statement->executeQuery();

        return $result->fetchOne() > 0;
    }

    private function hasAnonymousUserAlreadyRated(int $ratingId, string $sessionId): bool
    {
        $query     = 'SELECT count(*) FROM tl_rateit_ratings WHERE pid=:ratingId and session_id=:sessionId';
        $statement = $this->connection->prepare($query);
        $statement->bindValue('ratingId', $ratingId);
        $statement->bindValue('sessionId', $sessionId);
        $result = $statement->executeQuery();

        return $result->fetchOne() > 0;
    }

    private function areDuplicatesAllowed(): bool
    {
        return (bool) $this->framework->getAdapter(Config::class)->get('rating_allow_duplicate_ratings');
    }

    private function areDuplicatesAllowedForMembers(): bool
    {
        return (bool) $this->framework->getAdapter(Config::class)->get('rating_allow_duplicate_ratings_for_members');
    }
}
