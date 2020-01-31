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
use Hofff\Contao\RateIt\Rating\Comments\CommentsConfigurationLoader;
use Hofff\Contao\RateIt\Rating\Comments\CommentsTitleGenerator;

final class CommentsRatingType extends BaseRatingType
{
    /** @var CommentsConfigurationLoader */
    private $configurationLoader;

    /** @var CommentsTitleGenerator */
    private $titleGenerator;

    public function __construct(CommentsConfigurationLoader $configurationLoader, CommentsTitleGenerator $titleGenerator)
    {
        $this->configurationLoader = $configurationLoader;
        $this->titleGenerator      = $titleGenerator;
    }

    public function name() : string
    {
        return 'comments';
    }

    protected function determineParentPublishedState(int $sourceId) : ?bool
    {
        $comment = $this->loadComment($sourceId);
        if ($comment) {
            return (bool) $comment->published;
        }

        return null;
    }

    public function determineActiveState(int $sourceId) : bool
    {
        $comment = $this->loadComment($sourceId);
        if ($comment === null) {
            return false;
        }

        $configuration = $this->configurationLoader->load($comment->source, $comment->parent);
        if (!$configuration) {
            return false;
        }

        return (bool) $configuration->addCommentsRating;
    }

    public function generateTitle(int $sourceId) : string
    {
        $comment = $this->loadComment($sourceId);
        if ($comment === null) {
            return sprintf('Comment ID %s', $sourceId);
        }

        return $this->titleGenerator->generate($comment->name, $comment->source, $comment->parent);
    }

    private function loadComment(int $sourceId): ?CommentsModel
    {
        return CommentsModel::findByPk($sourceId);
    }
}
