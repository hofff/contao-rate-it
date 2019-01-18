<?php

declare(strict_types=1);

namespace Hofff\Contao\RateIt\EventListener\Dca;

use Contao\DataContainer;
use Hofff\Contao\RateIt\EventListener\Dca\BaseDcaListener;

final class FaqBaseDcaListener extends BaseDcaListener
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
        return $this->insertOrUpdateRatingKey($dc, 'faq', $dc->activeRecord->question);
    }

    public function delete(DataContainer $dc)
    {
        return $this->deleteRatingKey($dc, 'faq');
    }
}
