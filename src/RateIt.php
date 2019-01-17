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
 * @copyright  2012-2019 hofff.com
 * @license    https://github.com/hofff/contao-rate-it/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace Hofff\Contao\RateIt;

use Contao\Environment;
use Contao\Frontend;
use Symfony\Component\HttpFoundation\JsonResponse;

class RateIt extends Frontend
{
    var $allowDuplicates = false;
    var $rateItFrontend;

    /**
     * Initialize the controller
     */
    public function __construct()
    {
        parent::__construct();

        // See #4099
        if (! defined('BE_USER_LOGGED_IN')) {
            define('BE_USER_LOGGED_IN', false);
        }
        if (! defined('FE_USER_LOGGED_IN')) {
            define('FE_USER_LOGGED_IN', false);
        }

        $this->rateItFrontend = new RateItFrontend();

        $this->loadLanguageFile('default');
        $this->allowDuplicates           = $GLOBALS['TL_CONFIG']['rating_allow_duplicate_ratings'];
        $this->allowDuplicatesForMembers = $GLOBALS['TL_CONFIG']['rating_allow_duplicate_ratings_for_members'];
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
    public function doVote()
    {
        $ip = $_SERVER['REMOTE_ADDR'];

        $rkey    = $this->Input->post('id');
        $percent = $this->Input->post('vote');
        $type    = $this->Input->post('type');

        //Make sure that the ratable ID is a number and not something crazy.
        if (strstr($rkey, '|')) {
            $arrRkey = explode('|', $rkey);
            foreach ($arrRkey as $key) {
                if (! is_numeric($key)) {
                    return new JsonResponse($GLOBALS['TL_LANG']['rateit']['error']['invalid_rating']);
                }
                $id = $rkey;
            }
        } else {
            if (is_numeric($rkey)) {
                $id = $rkey;
            } else {
                return new JsonResponse($GLOBALS['TL_LANG']['rateit']['error']['invalid_rating']);
            }
        }

        //Make sure the percent is a number and under 100.
        if (is_numeric($percent) && $percent < 101) {
            $rating = $percent;
        } else {
            return new JsonResponse($GLOBALS['TL_LANG']['rateit']['error']['invalid_rating']);
        }

        //Make sure that the ratable type is 'page' or 'ce' or 'module'
        if (! ($type === 'page' || $type === 'article' || $type === 'ce' || $type === 'module' || $type === 'news' || $type === 'faq' || $type === 'galpic' || $type === 'news4ward')) {
            return new JsonResponse($GLOBALS['TL_LANG']['rateit']['error']['invalid_type']);
        }

        $strHash = sha1(session_id() . (! $GLOBALS['TL_CONFIG']['disableIpCheck'] ? Environment::get('ip') : '') . 'FE_USER_AUTH');

        // FrontendUser auslesen
        if (FE_USER_LOGGED_IN) {
            $objUser = $this->Database->prepare("SELECT pid FROM tl_session WHERE hash=?")
                ->limit(1)
                ->execute($strHash);

            if ($objUser->numRows) {
                $userId = $objUser->pid;
            }
        }


        $ratableKeyId = $this->Database->prepare('SELECT id FROM tl_rateit_items WHERE rkey=? and typ=?')
            ->execute($id, $type)
            ->fetchAssoc();

        $canVote = false;
        if (isset($userId)) {
            $countUser = $this->Database->prepare('SELECT * FROM tl_rateit_ratings WHERE pid=? and memberid=?')
                ->execute($ratableKeyId['id'], $userId)
                ->count();
        }
        $countIp = $this->Database->prepare('SELECT * FROM tl_rateit_ratings WHERE pid=? and ip_address=?')
            ->execute($ratableKeyId['id'], $ip)
            ->count();

        // Die with an error if the insert fails (duplicate IP or duplicate member id for a vote).
        if ((! $this->allowDuplicatesForMembers && (isset($countUser) ? $countUser == 0 : false)) || ($this->allowDuplicatesForMembers && isset($userId))) {
            // Insert the data.
            $arrSet = array('pid'        => $ratableKeyId['id'],
                            'tstamp'     => time(),
                            'ip_address' => $ip,
                            'memberid'   => isset($userId) ? $userId : null,
                            'rating'     => $rating,
                            'createdat'  => time(),
            );
            $this->Database->prepare('INSERT INTO tl_rateit_ratings %s')
                ->set($arrSet)
                ->execute();
        } elseif (! isset($countUser) && ((! $this->allowDuplicates && $countIp == 0) || $this->allowDuplicates)) {
            // Insert the data.
            $arrSet = array('pid'        => $ratableKeyId['id'],
                            'tstamp'     => time(),
                            'ip_address' => $ip,
                            'memberid'   => isset($userId) ? $userId : null,
                            'rating'     => $rating,
                            'createdat'  => time(),
            );
            $this->Database->prepare('INSERT INTO tl_rateit_ratings %s')
                ->set($arrSet)
                ->execute();
        } else {
            return new JsonResponse($GLOBALS['TL_LANG']['rateit']['error']['duplicate_vote']);
        }

        $rating = $this->rateItFrontend->loadRating($id, $type);

        return new JsonResponse($this->rateItFrontend->getStarMessage($rating));
    }
}
