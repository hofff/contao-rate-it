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

namespace Hofff\Contao\RateIt\Rating\Comments;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Model;
use function class_exists;

final readonly class CommentsConfigurationLoader
{
    public function __construct(private ContaoFramework $framework, private array $supportedSources)
    {
    }

    public function load(string $source, int $parent, bool $checkSupported = true) : ?Model
    {
        if ($checkSupported && ! isset($this->supportedSources[$source])) {
            return null;
        }

        $this->framework->initialize();

        $modelClass = Model::getClassFromTable($source);
        if (! class_exists($modelClass)) {
            return null;
        }

        /** @var Model $modelClass */
        $parentRecord = $modelClass::findByPk($parent);
        if (! $parentRecord) {
            return null;
        }

        if (! $checkSupported || $source === $this->supportedSources[$source]) {
            return $parentRecord;
        }

        if (! isset($GLOBALS['TL_DCA'][$parentRecord::getTable()]['config']['ptable'])
            || $GLOBALS['TL_DCA'][$parentRecord::getTable()]['config']['ptable'] === $source) {
            return null;
        }

        return $this->load(
            $GLOBALS['TL_DCA'][$parentRecord::getTable()]['config']['ptable'],
            (int) $parentRecord->pid,
            false
        );
    }
}
