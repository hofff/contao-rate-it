<?php

declare(strict_types=1);

namespace Hofff\Contao\RateIt\EventListener\Dca;

use Contao\CommentsModel;
use Contao\DataContainer;
use Hofff\Contao\RateIt\Rating\Comments\CommentsConfigurationLoader;
use Hofff\Contao\RateIt\Rating\Comments\CommentsTitleGenerator;

final class CommentsDcaListener extends BaseDcaListener
{
    /** @var CommentsTitleGenerator */
    private $titleGenerator;

    /** @var CommentsConfigurationLoader */
    private $configurationLoader;

    public function __construct(
        CommentsConfigurationLoader $configurationLoader,
        CommentsTitleGenerator $titleGenerator,
        array $activeItems
    )
    {
        parent::__construct($activeItems);

        $this->configurationLoader = $configurationLoader;
        $this->titleGenerator      = $titleGenerator;
    }

    public function onLoad() : void
    {
        if (! $this->isActive('comments')) {
            return;
        }

        $dca = &$GLOBALS['TL_DCA']['tl_comments'];

        $dca['config']['onsubmit_callback'][]          = [self::class, 'insert'];
        $dca['config']['ondelete_callback'][]          = [self::class, 'delete'];
        $dca['config']['onrestore_version_callback'][] = [self::class, 'onRestore'];
    }

    public function insert(DataContainer $dc) : void
    {
        $comment = CommentsModel::findByPk($dc->id);
        if ($comment === null) {
            return;
        }

        $title = $this->titleGenerator->generate(
            $comment->name,
            $comment->source,
            $comment->parent
        );

        $configuration = $this->configurationLoader->load($comment->source, $comment->parent);
        $active        = $configuration && $configuration->addCommentsRating;

        $this->updateRatingKey($comment->id, 'comments', $title, $comment->published, $active);
    }

    public function delete(DataContainer $dc) : void
    {
        $configuration = $this->configurationLoader->load($dc->activeRecord->source, $dc->activeRecord->parent);
        if (! $configuration || ! $configuration->addCommentsRating) {
            return;
        }

        $this->onDeleteItemUpdateRating($dc, 'comments');
    }

    public function onRestore(string $table, $insertId, $version, array $data) : void
    {
        $configuration = $this->configurationLoader->load($data['source'], $data['parent']);
        if (! $configuration || ! $configuration->addCommentsRating) {
            return;
        }

        $this->restore($insertId, 'comments', $data['published']);
    }

    public function onUndo(string $table, array $row) : void
    {
        if (! $this->isActive('comments')) {
            return;
        }

        $configuration = $this->configurationLoader->load($row['source'], $row['parent']);
        if (! $configuration || ! $configuration->addCommentsRating) {
            return;
        }

        $this->restore($row['id'], 'comments', $row['published']);
    }
}
