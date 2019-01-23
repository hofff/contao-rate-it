<?php

declare(strict_types=1);

namespace Hofff\Contao\RateIt\EventListener\Dca;

use Contao\DataContainer;
use Hofff\Contao\RateIt\EventListener\Dca\BaseDcaListener;

final class ArticleBaseDcaListener extends BaseDcaListener
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function insert(DataContainer $dc) : void
    {
        $this->insertOrUpdateRatingKey($dc, 'article', $dc->activeRecord->title);
    }

    public function delete(DataContainer $dc) : void
    {
        $this->deleteRatingKey($dc, 'article');
    }
}
