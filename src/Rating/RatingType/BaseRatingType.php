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

use Doctrine\DBAL\Connection;
use Hofff\Contao\RateIt\Rating\RatingType;
use Hofff\Contao\RateIt\Rating\SourceInformation;
use PDO;

abstract class BaseRatingType implements RatingType
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function sourceInformation(int $sourceId) : ?SourceInformation
    {
        $record = $this->loadRecord($sourceId);
        if ($record === null) {
            return null;
        }

        return new SourceInformation(
            $this->generateTitle($record),
            $this->determineActiveState($record),
            $this->determineParentStatus($record)
        );
    }

    protected function determineParentStatus(array  $record) : string
    {
        $published = $this->determineParentPublishedState($record);

        switch ($published) {
            case true:
                return 'a';

            case false:
                return 'i';

            case null:
            default:
                return 'r';
        }
    }

    protected function loadRecord(int $sourceId): ?array
    {
        $statement = $this->connection->prepare(sprintf('SELECT * FROM %s WHERE id=? LIMIT 0,1', $this->tableName()));
        $statement->execute([$sourceId]);

        if ($statement->rowCount() === 0) {
            return null;
        }

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    abstract protected function tableName() : string;

    abstract protected function generateTitle(array $record) : string;

    abstract protected function determineActiveState(array $record) : bool;

    abstract protected function determineParentPublishedState(array $record) : bool;
}
