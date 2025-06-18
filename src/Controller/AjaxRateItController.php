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

use function explode;
use function in_array;
use function is_numeric;
use function str_contains;
use function time;

final class AjaxRateItController
{
    /** @param string[] $ratingTypes */
    public function __construct(
        private readonly Connection $connection,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly TranslatorInterface $translator,
        private readonly ContaoFramework $framework,
        private readonly RatingService $ratingService,
        private readonly IsUserAllowedToRate $isUserAllowedToRate,
        private readonly array $ratingTypes,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $this->framework->initialize();

        return $this->doVote($request);
    }

    /**
     * This is the function in charge of handling a vote and saving it to the
     * database.
     */
    public function doVote(Request $request): Response
    {
        $rkey    = (string) $request->request->get('id');
        $percent = $request->request->get('vote');
        $type    = $request->request->get('type');
        $itemId  = null;

        //Make sure that the ratable ID is a number and not something crazy.
        if (str_contains($rkey, '|')) {
            $arrRkey = explode('|', $rkey);
            foreach ($arrRkey as $key) {
                if (! is_numeric($key)) {
                    return new Response(
                        $this->translator->trans('rateit.error.invalid_rating', [], 'contao_default'),
                        400,
                    );
                }

                $itemId = $rkey;
            }
        } else {
            if (! is_numeric($rkey)) {
                return new JsonResponse(
                    [
                        'title' => $this->translator->trans('rateit.error.invalid_rating', [], 'contao_default'),
                        'status' => 400,
                    ],
                    400,
                );
            }

            $itemId = $rkey;
        }

        //Make sure the percent is a number and under 100.
        if (! is_numeric($percent) || $percent >= 101) {
            return new JsonResponse(
                [
                    'title' => $this->translator->trans('rateit.error.invalid_rating', [], 'contao_default'),
                    'status' => 400,
                ],
                400,
            );
        }

        $rating = $percent;

        //Make sure that the ratable type is supported
        if (! in_array($type, $this->ratingTypes, true)) {
            return new JsonResponse(
                [
                    'title' => $this->translator->trans('rateit.error.invalid_type', [], 'contao_default'),
                    'status' => 400,
                ],
                400,
            );
        }

        $userId       = $this->determineUserId();
        $ratableKeyId = $this->getRateableKeyId((int) $itemId, $type);
        $sessionId    = new CurrentUserId();

        if (! $this->isUserAllowedToRate->__invoke($ratableKeyId, (string) $sessionId, $userId)) {
            return new JsonResponse(
                [
                    'title' => $this->translator->trans('rateit.error.duplicate_vote', [], 'contao_default'),
                    'status' => 400,
                ],
                400,
            );
        }

        $this->connection->insert(
            'tl_rateit_ratings',
            [
                'pid'        => $ratableKeyId,
                'tstamp'     => time(),
                'session_id' => (string) $sessionId,
                'memberid'   => $userId,
                'rating'     => $rating,
                'createdat'  => time(),
            ],
        );

        return new JsonResponse(
            [
                'status' => 200,
                'data'   => $this->ratingService->getRatingWithSuccessMessage($type, (int) $itemId, $userId),
            ],
        );
    }

    private function determineUserId(): int|null
    {
        $token = $this->tokenStorage->getToken();
        if (! $token) {
            return null;
        }

        $user = $token->getUser();
        if ($user instanceof FrontendUser) {
            return $user->id;
        }

        return null;
    }

    protected function getRateableKeyId(int $itemId, string $type): int
    {
        $statement = $this->connection->prepare('SELECT id FROM tl_rateit_items WHERE rkey=:id and typ=:type');
        $statement->bindValue('id', $itemId);
        $statement->bindValue('type', $type);
        $result = $statement->executeQuery();

        return (int) $result->fetchOne();
    }
}
