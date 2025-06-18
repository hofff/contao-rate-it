<?php

declare(strict_types=1);

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

use Contao\FrontendTemplate;
use Contao\Model;
use Contao\Model\Collection;

/** @psalm-suppress PropertyNotSetInConstructor */
final class RateItRating extends RateItFrontend
{
    public int $ratingKey = 0;

    public string $ratingType = 'page';

    public function __construct(Model|Collection|null $objElement = null)
    {
        parent::__construct($objElement);
    }

    /**
     * Compile
     */
    #[\Override]
    protected function compile(): void
    {
        $this->loadLanguageFile('default');

        $this->Template = new FrontendTemplate($this->strTemplate);
        $this->Template->setData($this->arrData);

        $rating   = $this->loadRating($this->ratingKey, $this->ratingType);
        $ratingId = $this->ratingKey;
        $stars    = ! $rating ? 0 : $this->percentToStars((float) $rating['rating']);

        $this->Template->descriptionId = 'rateItRating-' . $ratingId . '-description';
        $this->Template->description   = $this->getStarMessage($rating);
        $this->Template->id            = 'rateItRating-' . $ratingId . '-' . $this->ratingType . '-' . (string) $stars . '_' . $this->intStars;
        $this->Template->class         = 'rateItRating';
        $this->Template->itemreviewed  = $rating['title'] ?? null;
        $this->Template->actRating     = $this->percentToStars((float) ($rating['rating'] ?? 0));
        $this->Template->maxRating     = $this->intStars;
        $this->Template->votes         = $rating['totalRatings'] ?? null;

        if ($this->strTextPosition === "before") {
            $this->Template->showBefore = true;
        } else if ($this->strTextPosition === "after") {
            $this->Template->showAfter = true;
        }
    }
}
