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

namespace Hofff\Contao\RateIt\Frontend;

use Contao\Hybrid;
use Contao\Model;
use Contao\Model\Collection;
use Contao\Template;
use Hofff\Contao\RateIt\Rating\RatingService;

use function assert;
use function intval;

/**
 * @property Template   $Template
 * @property int|string $id
 * @property string     $name
 * @property string     $rateit_title
 * @psalm-suppress PropertyNotSetInConstructor
 */
class RateItFrontend extends Hybrid
{
    /** Primary key */
    protected string $strPk = 'id';

    /** Template */
    protected $strTemplate = 'rateit_default';

    /** Anzahl der Herzen/Sterne */
    protected int $intStars = 5;

    /** Textposition */
    protected string $strTextPosition = 'after';

    public function __construct(Model|Collection|null $objElement = null)
    {
        if (! empty($objElement)) {
            if ($objElement instanceof Model) {
                $this->strTable = $objElement->getTable();
            } else {
                $this->strTable = $objElement->current()->getTable();
            }

            $this->strKey = $this->strPk;
        }

        $stars = intval($GLOBALS['TL_CONFIG']['rating_count']);
        if ($stars > 0) {
            $this->intStars = $stars;
        }

        /** @psalm-suppress InvalidArgument */
        parent::__construct($objElement);
    }

    #[\Override]
    public function generate(): string
    {
        $this->loadLanguageFile('default');
        $stars = intval($GLOBALS['TL_CONFIG']['rating_count']);
        if ($stars > 0) {
            $this->intStars = $stars;
        }
        $this->strTemplate     = $GLOBALS['TL_CONFIG']['rating_template'];
        $this->strTextPosition = $GLOBALS['TL_CONFIG']['rating_textposition'];

        return parent::generate();
    }

    #[\Override]
    protected function compile(): void
    {
    }

    public function getStarMessage(array|null $rating): string
    {
        $service = self::getContainer()->get(RatingService::class);
        assert($service instanceof RatingService);

        return $service->getStarMessage($rating);
    }

    protected function loadRating(int $ratingKey, string $ratingType): array|null
    {
        $service = self::getContainer()->get(RatingService::class);
        assert($service instanceof RatingService);

        return $service->loadRating($ratingKey, $ratingType);
    }

    protected function percentToStars(float $percent): float
    {
        $modifier = (float) (100 / $this->intStars);

        return round($percent / $modifier, 1);
    }
}
