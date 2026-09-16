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
use Override;

use function sprintf;

abstract class BaseRatingType implements RatingType
{
    public function __construct(private readonly Connection $connection)
    {
    }

    #[Override]
    public function sourceInformation(int $sourceId): SourceInformation|null
    {
        $record = $this->loadRecord($sourceId);
        if ($record === null) {
            return null;
        }

        return new SourceInformation(
            $this->generateTitle($record),
            $this->determineActiveState($record),
            $this->determineParentStatus($record),
        );
    }

    /** @param array<string, mixed> $record */
    protected function determineParentStatus(array $record): string
    {
        return $this->determineParentPublishedState($record) ? 'a' : 'i';
    }

    protected function loadRecord(int $sourceId): array|null
    {
        $result = $this->connection->executeQuery(
            sprintf('SELECT * FROM %s WHERE id=? LIMIT 0,1', $this->tableName()),
            [$sourceId],
        );

        if ($result->rowCount() === 0) {
            return null;
        }

        return (array) $result->fetchAssociative();
    }

    abstract protected function tableName(): string;

    /** @param array<string, mixed> $record */
    abstract protected function generateTitle(array $record): string;

    /** @param array<string, mixed> $record */
    abstract protected function determineActiveState(array $record): bool;

    /** @param array<string, mixed> $record */
    abstract protected function determineParentPublishedState(array $record): bool;
}
