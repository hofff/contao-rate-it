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

namespace Hofff\Contao\RateIt\Controller;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\FrontendUser;
use Doctrine\DBAL\Connection;
use Hofff\Contao\RateIt\Rating\CurrentUserId;
use Hofff\Contao\RateIt\Rating\IsUserAllowedToRate;
use Hofff\Contao\RateIt\Rating\RatingService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function in_array;

class AjaxRateItController
{
    public function __construct(
        private readonly Connection $connection,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly TranslatorInterface $translator,
        private readonly ContaoFramework $framework,
        private readonly RatingService $ratingService,
        private readonly IsUserAllowedToRate $isUserAllowedToRate,
        /** @var string[] */
        private readonly array $ratingTypes
    ) {
    }

    public function __invoke(Request $request) : Response
    {
        $this->framework->initialize();

        // See #4099
        if (! defined('BE_USER_LOGGED_IN')) {
            define('BE_USER_LOGGED_IN', false);
        }
        if (! defined('FE_USER_LOGGED_IN')) {
            define('FE_USER_LOGGED_IN', false);
        }

        return $this->doVote($request);
    }

    /**
     * doVote
     *
     * This is the function in charge of handling a vote and saving it to the
     * database.
     *
     * NOTE: This method is meant to be called as part of an AJAX request.  As
     * such, it unitlizes the die() function to display its errors.  THIS
     * WOULD BE A VERY BAD FUNCTION TO CALL FROM WITHIN ANOTHER PAGE.
     *
     * @param integer id      - The id of key to register a rating for.
     * @param integer percent - The rating in percentages.
     */
    public function doVote(Request $request)
    {
        $rkey     = $request->request->get('id');
        $percent  = $request->request->get('vote');
        $type     = $request->request->get('type');
        $itemId   = null;

        //Make sure that the ratable ID is a number and not something crazy.
        if (str_contains($rkey, '|')) {
            $arrRkey = explode('|', $rkey);
            foreach ($arrRkey as $key) {
                if (! is_numeric($key)) {
                    return new Response(
                        $this->translator->trans('rateit.error.invalid_rating', [], 'contao_default'),
                        400
                    );
                }
                $itemId = $rkey;
            }
        } else {
            if (is_numeric($rkey)) {
                $itemId = $rkey;
            } else {
                return new JsonResponse(
                    [
                        'title' => $this->translator->trans('rateit.error.invalid_rating', [], 'contao_default'),
                        'status' => 400
                    ],
                    400
                );
            }
        }

        //Make sure the percent is a number and under 100.
        if (is_numeric($percent) && $percent < 101) {
            $rating = $percent;
        } else {
            return new JsonResponse(
                [
                    'title' => $this->translator->trans('rateit.error.invalid_rating', [], 'contao_default'),
                    'status' => 400
                ],
                400
            );
        }

        //Make sure that the ratable type is supported
        if (! in_array($type, $this->ratingTypes, true)) {
            return new JsonResponse(
                [
                    'title' => $this->translator->trans('rateit.error.invalid_type', [], 'contao_default'),
                    'status' => 400
                ],
                400
            );
        }

        $userId       = $this->determineUserId();
        $ratableKeyId = $this->getRateableKeyId($itemId, $type);
        $sessionId    = new CurrentUserId();

        if (! $this->isUserAllowedToRate->__invoke($ratableKeyId, (string) $sessionId, $userId)) {
            return new JsonResponse(
                [
                    'title' => $this->translator->trans('rateit.error.duplicate_vote', [], 'contao_default'),
                    'status' => 400
                ],
                400
            );
        }

        $this->connection->insert(
            'tl_rateit_ratings',
            ['pid'        => $ratableKeyId,
             'tstamp'     => time(),
             'session_id' => (string) $sessionId,
             'memberid'   => $userId,
             'rating'     => $rating,
             'createdat'  => time(),
            ]
        );

        return new JsonResponse(
            [
                'status' => 200,
                'data'   => $this->ratingService->getRatingWithSuccessMessage($type, $itemId, $userId),
            ]
        );
    }

    private function determineUserId() : ?int
    {
        $token = $this->tokenStorage->getToken();
        if (! $token) {
            return null;
        }

        $user = $token->getUser();
        if ($user instanceof FrontendUser) {
            return (int)$user->id;
        }

        return null;
    }

    protected function getRateableKeyId($itemId, string $type) : int
    {
        $statement = $this->connection->prepare('SELECT id FROM tl_rateit_items WHERE rkey=:id and typ=:type');
        $statement->bindValue('id', $itemId);
        $statement->bindValue('type', $type);
        $result = $statement->executeQuery();

        return (int) $result->fetchOne();
    }
}
