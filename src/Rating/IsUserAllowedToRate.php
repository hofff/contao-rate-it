<?php

declare(strict_types=1);

namespace Hofff\Contao\RateIt\Rating;

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Doctrine\DBAL\Connection;
use PDO;

final class IsUserAllowedToRate
{
    /** @var Connection */
    private $connection;

    /** @var ContaoFrameworkInterface */
    private $framework;

    public function __construct(Connection $connection, ContaoFrameworkInterface $framework)
    {
        $this->connection = $connection;
        $this->framework  = $framework;
    }

    public function __invoke(int $ratingId, string $clientIp, ?int $userId) : bool
    {
        $this->framework->initialize();

        if ($userId) {
            if ($this->areDuplicatesAllowedForMembers()) {
                return true;
            }

            return $this->hasLoggedInUserAlreadyRated($ratingId, $userId);
        }

        if ($this->areDuplicatesAllowed()) {
            return true;
        }

        return !$this->hasAnonymousUserAlreadyRated($ratingId, $clientIp);
    }

    private function hasLoggedInUserAlreadyRated(int $ratingId, int $userId) : bool
    {
        $statement = $this->connection->prepare(
            'SELECT count(*) FROM tl_rateit_ratings WHERE pid=:pid and memberid=:memberid'
        );

        $statement->bindValue('pid', $ratingId);
        $statement->bindValue('memberid', $userId);
        $statement->execute();

        return $statement->fetch(PDO::FETCH_COLUMN) > 0;
    }

    private function hasAnonymousUserAlreadyRated(int $ratingId, string $clientIp) : bool
    {
        $query     = 'SELECT count(*) FROM tl_rateit_ratings WHERE pid=:ratingId and ip_address=:clientIp';
        $statement = $this->connection->prepare($query);
        $statement->bindValue('ratingId', $ratingId);
        $statement->bindValue('clientIp', $clientIp);
        $statement->execute();

        return $statement->fetch(PDO::FETCH_COLUMN) > 0;
    }

    private function areDuplicatesAllowed() : bool
    {
        return (bool) $this->framework->getAdapter(Config::class)->get('rating_allow_duplicate_ratings');
    }

    private function areDuplicatesAllowedForMembers() : bool
    {
        return (bool) $this->framework->getAdapter(Config::class)->get('rating_allow_duplicate_ratings_for_members');
    }
}
