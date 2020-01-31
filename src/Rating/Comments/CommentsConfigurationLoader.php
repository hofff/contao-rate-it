<?php

declare(strict_types=1);

namespace Hofff\Contao\RateIt\Rating\Comments;


use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Model;
use function class_exists;

final class CommentsConfigurationLoader
{
    /** @var ContaoFrameworkInterface */
    private $framework;

    private $supportedSources = [
        'tl_news' => 'tl_news_archive'
    ];

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    public function load(string $source, $parent, bool $checkSupportedSources = true) : ?Model
    {
        if ($checkSupportedSources && ! isset($this->supportedSources[$source])) {
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

        if (! $checkSupportedSources || $source === $this->supportedSources[$source]) {
            return $parentRecord;
        }

        if (! isset($GLOBALS['TL_DCA'][$parentRecord::getTable()]['config']['ptable'])
            || $GLOBALS['TL_DCA'][$parentRecord::getTable()]['config']['ptable'] === $source) {
            return null;
        }

        return $this->load(
            $GLOBALS['TL_DCA'][$parentRecord::getTable()]['config']['ptable'],
            $parentRecord->pid,
            false
        );
    }
}
