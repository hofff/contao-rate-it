<?php

declare(strict_types=1);

namespace Hofff\Contao\RateIt\EventListener\Dca;

use Contao\DataContainer;
use Hofff\Contao\RateIt\EventListener\Dca\BaseDcaListener;

final class ModuleBaseDcaListener extends BaseDcaListener
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
        return $this->insertOrUpdateRatingKey($dc, 'module', $dc->activeRecord->rateit_title);
    }

    public function delete(DataContainer $dc)
    {
        return $this->deleteRatingKey($dc, 'module');
    }

    public function getRateItTopModuleTemplates()
    {
        return $this->getTemplateGroup('mod_rateit_top');
    }
}
