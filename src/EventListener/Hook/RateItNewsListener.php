<?php

/**
 * This file is part of hofff/contao-content.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author     Carsten Götzinger <info@cgo-it.de>
 * @author     David Molineus <david@hofff.com>
 * @copyright  2013-2018 cgo IT.
 * @copyright  2019 hofff.com.
 * @license    https://github.com/hofff/contao-rate-it/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace Hofff\Contao\RateIt\EventListener\Hook;

use Contao\ModuleNews;
use Contao\Template;
use Hofff\Contao\RateIt\Frontend\RateItFrontend;
use Hofff\Contao\RateIt\Rating\RatingService;

class RateItNewsListener extends RateItFrontend
{
    /** @var RatingService */
    private $ratingService;

    public function __construct(RatingService $ratingService)
    {
        parent::__construct();

        $this->ratingService = $ratingService;
    }

    public function parseArticle(Template $template, array $newsArticle, $caller) : void
    {
        if (!$caller instanceof ModuleNews || !$newsArticle['addRating']) {
            return;
        }

        $session = self::getContainer()->get('session');
        $sessionId = null;
        if ($session->isStarted()) {
            $sessionId = $session->getId();
        }

        // TODO: Do not use Environment and User class. Use symfony equivalents here
        $template->ratit_template = $this->Config::get('rating_template') ?: 'rateit_default';
        $template->rating         = $this->ratingService->getRating(
            'news',
            (int) $template->id,
            $sessionId,
            (int) $this->User->id
        );
    }
}
