<?php

declare(strict_types=1);

namespace Hofff\Contao\RateIt\EventListener\Dca;

use Contao\DataContainer;
use Hofff\Contao\RateIt\DcaHelper;

final class NewsDcaListener extends DcaHelper
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function insert(DataContainer $dc)
    {
        return $this->insertOrUpdateRatingKey($dc, 'news', $dc->activeRecord->headline);
    }

    public function delete(DataContainer $dc)
    {
        return $this->deleteRatingKey($dc, 'news');
    }
}
