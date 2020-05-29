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

namespace Hofff\Contao\RateIt\EventListener\Hook;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Template;
use Doctrine\DBAL\Connection;
use Hofff\Contao\RateIt\Rating\Comments\CommentsConfigurationLoader;
use Hofff\Contao\RateIt\Rating\RatingService;
use Hofff\Contao\RateIt\Rating\Comments\CommentsTitleGenerator;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use function strncmp;
use function substr;
use function time;

final class RateItCommentsListener extends RatingListener
{
    /** @var Connection */
    private $connection;

    /** @var CommentsTitleGenerator */
    private $titleGenerator;

    /** @var CommentsConfigurationLoader */
    private $configurationLoader;

    public function __construct(
        RatingService $ratingService,
        TokenStorageInterface $tokenStorage,
        ContaoFrameworkInterface $framework,
        CommentsConfigurationLoader $configurationLoader,
        CommentsTitleGenerator $titleGenerator,
        Connection $connection
    )
    {
        parent::__construct($ratingService, $tokenStorage, $framework);

        $this->connection          = $connection;
        $this->titleGenerator      = $titleGenerator;
        $this->configurationLoader = $configurationLoader;
    }

    private $supportedSources = [
        'tl_news' => 'tl_news_archive',
    ];

    public function onParseTemplate(Template $template) : void
    {
        if (strncmp($template->getName(), 'com_', 4) !== 0) {
            return;
        }

        $configuration = $this->configurationLoader->load($template->source, $template->parent);
        if (! $configuration || ! $configuration->addCommentsRating) {
            return;
        }

        $template->ratit_template  = $this->getRatingTemplate();
        $template->rateit_position = $configuration->rateit_position_comments;
        $template->rating          = $this->getCommentRating($template);
    }

    private function getCommentRating(Template $template) : ?array
    {
        $commentId = (int)substr($template->id, 1);
        $rating    = $this->getRating('comments', $commentId);
        if ($rating !== null) {
            return $rating;
        }

        $this->connection->insert(
            'tl_rateit_items',
            [
                'rkey'         => $commentId,
                'tstamp'       => time(),
                'typ'          => 'comments',
                'createdat'    => time(),
                'title'        => $this->titleGenerator->generate(
                    $template->name,
                    $template->source,
                    $template->parent
                ),
                'active'       => '1',
                'parentstatus' => 'a',
            ]
        );

        return $this->getRating('comments', $commentId);
    }
}
