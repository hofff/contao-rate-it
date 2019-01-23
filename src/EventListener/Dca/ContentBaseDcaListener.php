<?php

declare(strict_types=1);

namespace Hofff\Contao\RateIt\EventListener\Dca;

use Contao\DataContainer;
use Contao\File;
use Contao\FilesModel;
use Contao\StringUtil;
use Hofff\Contao\RateIt\EventListener\Dca\BaseDcaListener;

final class ContentBaseDcaListener extends BaseDcaListener
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
        return $this->insertOrUpdateRatingKey($dc, 'ce', $dc->activeRecord->rateit_title);
    }

    public function delete(DataContainer $dc)
    {
        return $this->deleteRatingKey($dc, 'ce');
    }
}
