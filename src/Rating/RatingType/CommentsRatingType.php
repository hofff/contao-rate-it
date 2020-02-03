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

use Contao\CommentsModel;
use Doctrine\DBAL\Connection;
use Hofff\Contao\RateIt\Rating\Comments\CommentsConfigurationLoader;
use Hofff\Contao\RateIt\Rating\Comments\CommentsTitleGenerator;

final class CommentsRatingType extends BaseRatingType
{
    /** @var CommentsConfigurationLoader */
    private $configurationLoader;

    /** @var CommentsTitleGenerator */
    private $titleGenerator;

    public function __construct(
        Connection $connection,
        CommentsConfigurationLoader $configurationLoader,
        CommentsTitleGenerator $titleGenerator
    ) {
        parent::__construct($connection);

        $this->configurationLoader = $configurationLoader;
        $this->titleGenerator      = $titleGenerator;
    }

    public function name() : string
    {
        return 'comments';
    }

    public function determineActiveState(array $record) : bool
    {
        $configuration = $this->configurationLoader->load($record['source'], $record['parent']);
        if (!$configuration) {
            return false;
        }

        return (bool) $configuration->addCommentsRating;
    }

    public function generateTitle(array $record) : string
    {
        return $this->titleGenerator->generate($record['name'], $record['source'], $record['parent']);
    }

    protected function tableName() : string
    {
        return CommentsModel::getTable();
    }

    protected function determineParentPublishedState(array $record) : bool
    {
        return (bool) $record['published'];
    }
}
