<?php

/**
 * This file is part of hofff/contao-rate-it.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author     David Molineus <david@hofff.com>
 * @author     Carsten Götzinger <info@cgo-it.de>
 * @copyright  2019 hofff.com.
 * @copyright  2013-2018 cgo IT.
 * @license    https://github.com/hofff/contao-rate-it/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace Hofff\Contao\RateIt\EventListener\Dca;

use Contao\DataContainer;

final class ModuleDcaListener extends BaseDcaListener
{
    /** @var string[] */
    private $supportedTypes;

    public function __construct(array $supportedTypes)
    {
        parent::__construct();

        $this->supportedTypes = $supportedTypes;
    }

    public function insert(DataContainer $dc) : void
    {
        $this->insertOrUpdateRatingKey($dc, 'module', $dc->activeRecord->rateit_title);
    }

    public function delete(DataContainer $dc) : void
    {
        $this->deleteRatingKey($dc, 'module');
    }

    public function getRateItTopModuleTemplates() : array
    {
        return self::getTemplateGroup('mod_rateit_top');
    }

    public function typeOptions(): array
    {
        return $this->supportedTypes;
    }
}
