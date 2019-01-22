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

namespace Hofff\Contao\RateIt\Backend;

use Contao\BackendModule;
use Contao\Input;

class RateItBackendModule extends BackendModule
{
    protected $strTemplate;
    protected $actions = array();

    protected $rateit;

    protected $tl_root;
    protected $tl_files;
    protected $languages;

    private $compiler;
    private $action = '';
    private $parameter = '';

    private $arrExportHeader;
    private $arrExportHeaderDetails;

    /**
     * Anzahl der Herzen/Sterne
     * @var int
     */
    protected $intStars = 5;

    protected $label;
    protected $labels;

    /**
     * Initialize the controller
     */
    public function __construct($objElement = array())
    {
        parent::__construct($objElement);

        $this->label  = $GLOBALS['TL_LANG']['rateit']['star'];
        $this->labels = $GLOBALS['TL_LANG']['rateit']['stars'];

        $this->actions = array(
            //	  act[0]			strTemplate					compiler
            array('', 'rateitbe_ratinglist', 'listRatings'),
            array('reset_ratings', '', 'resetRatings'),
            array('view', 'rateitbe_ratingview', 'viewRating'),
        );

        $this->loadLanguageFile('rateit_backend');
        $this->arrExportHeader        = &$GLOBALS['TL_LANG']['tl_rateit']['xls_headers'];
        $this->arrExportHeaderDetails = &$GLOBALS['TL_LANG']['tl_rateit']['xls_headers_detail'];
    }

    /**
     * Generate module:
     * - Display a wildcard in the back end
     * - Select the template and compiler in the front end
     * @return string
     */
    public function generate()
    {
        $this->rateit     = new \stdClass();
        $rateit           = &$this->rateit;
        $rateit->username = $this->BackendUser->username;
        $rateit->isadmin  = $this->BackendUser->isAdmin;

        $this->strTemplate = $this->actions[0][1];
        $this->compiler    = $this->actions[0][2];

        $act = Input::get('act');
        if (! $act) $act = Input::post('act');
        foreach ($this->actions as $action) {
            if ($act == $action[0]) {
                $this->parameter   = $act;
                $this->action      = $action[0];
                $this->strTemplate = $action[1];
                $this->compiler    = $action[2];
                break;
            }
        }

        $stars = intval($GLOBALS['TL_CONFIG']['rating_count']);
        if ($stars > 0) {
            $this->intStars = $stars;
        }

        return str_replace(array('{{', '}}'), array('[{]', '[}]'), parent::generate());
    } // generate

    /**
     * Compile module: common initializations and forwarding to distinct function compiler
     */
    protected function compile()
    {
        // hide module?
        $compiler = $this->compiler;
        if ($compiler == 'hide') return;

        // load other helpers
        $this->tl_root          = str_replace("\\", '/', TL_ROOT) . '/';
        $this->tl_files         = str_replace("\\", '/', $GLOBALS['TL_CONFIG']['uploadPath']) . '/';
        $this->Template->rateit = $this->rateit;

        // complete rateit initialization
        $rateit           = &$this->rateit;
        $rateit->f_link   = $this->createUrl(array($this->action => $this->parameter));
        $rateit->f_action = $this->compiler;
        $rateit->f_mode   = $this->action;
        $rateit->theme    = new RateItBackend();
        $rateit->backLink = $this->getReferer(true);
        $rateit->homeLink = $this->createUrl();

        // execute compiler
        $this->$compiler($this->parameter);
    } // compile

    /**
     * List the ratings
     */
    protected function listRatings()
    {
        $rateit         = &$this->Template->rateit;
        $rateit->f_page = 0;

        // returning from submit?
        if ($this->filterPost('rateit_action') == $rateit->f_action) {
            // get url parameters
            $rateit->f_typ    = trim(Input::post('rateit_typ'));
            $rateit->f_active = trim(Input::post('rateit_active'));
            $rateit->f_order  = trim(Input::post('rateit_order'));
            $rateit->f_page   = trim(Input::post('rateit_page'));
            $rateit->f_find   = trim(Input::post('rateit_find'));
            $this->Session->set(
                'rateit_settings',
                array(
                    'rateit_typ'   => $rateit->f_typ,
                    'rateit_order' => $rateit->f_order,
                    'rateit_page'  => $rateit->f_page,
                    'rateit_find'  => $rateit->f_find,
                )
            );
        } else {
            $stg = $this->Session->get('rateit_settings');
            if (is_array($stg)) {
                $rateit->f_typ    = trim($stg['rateit_typ']);
                $rateit->f_active = trim($stg['rateit_active']);
                $rateit->f_order  = trim($stg['rateit_order']);
                $rateit->f_page   = trim($stg['rateit_page']);
                $rateit->f_find   = trim($stg['rateit_find']);
            } // if
        } // if

        if ($rateit->f_order == '') $rateit->f_order = 'rating';
        //if (!isset($rateit->f_active)) $rateit->f_active = '-1';

        if (isset($GLOBALS['TL_CONFIG']['rating_listsize']))
            $perpage = (int)trim($GLOBALS['TL_CONFIG']['rating_listsize']);
        if (! isset($perpage) || $perpage < 0) $perpage = 10;

        if ($rateit->f_page >= 0 && $perpage > 0) {
            $options['first'] = $rateit->f_page * $perpage;
            $options['limit'] = $perpage;
        } // if
        if ($rateit->f_typ != '') $options['typ'] = $rateit->f_typ;
        if ($rateit->f_active != '') $options['active'] = $rateit->f_active == '0' ? '' : $rateit->f_active;
        if ($rateit->f_find != '') $options['find'] = $rateit->f_find;

        switch ($rateit->f_order) {
            case 'title'     :
                $options['order'] = 'title';
                break;
            case 'typ'         :
                $options['order'] = 'typ';
                break;
            case 'createdat' :
                $options['order'] = 'createdat';
                break;
            default             :
                $options['order'] = 'rating desc';
        } // switch

        // query extensions
        $rateit->ratingitems = $this->getRatingItems($options);
        if ($rateit->f_page >= 0 && $perpage > 0 && count($rateit->ratingitems) == 0) {
            $rateit->f_page      = 0;
            $options['first']    = 0;
            $rateit->ratingitems = $this->getRatingItems($options);
        } // if

        // add view links
        foreach ($rateit->ratingitems as &$ext) {
            $ext->viewLink = $this->createUrl(array('act' => 'view', 'rkey' => $ext->rkey, 'typ' => $ext->typ));
            $totrecs       = $ext->totcount;
        } // foreach

        // create pages list
        $rateit->pages = array();
        if ($perpage > 0) {
            $first = 1;
            while ($totrecs > 0) {
                $cnt             = $totrecs > $perpage ? $perpage : $totrecs;
                $rateit->pages[] = $first . ' - ' . ($first + $cnt - 1);
                $first           += $cnt;
                $totrecs         -= $cnt;
            } // while
        } // if
    } // listRatings

    /**
     * Detailed view of one rating.
     * @param string
     */
    protected function viewRating()
    {
        $rateit = &$this->Template->rateit;

        $rateit->f_page = 0;

        // returning from submit?
        if ($this->filterPost('rateit_action') == $rateit->f_action) {
            // get url parameters
            $rateit->f_page = trim(Input::post('rateit_details_page'));
            $this->Session->set(
                'rateit_settings',
                array(
                    'rateit_details_page' => $rateit->f_page,
                )
            );
        } else {
            $stg = $this->Session->get('rateit_settings');
            if (is_array($stg)) {
                $rateit->f_page = trim($stg['rateit_details_page']);
            } // if
        } // if

        $rkey = Input::get('rkey');
        if (strstr($rkey, '|')) {
            $arrRkey = explode('|', $rkey);
            foreach ($arrRkey as $key) {
                if (! is_numeric($key)) {
                    $this->redirect($rateit->homeLink);
                    exit;
                }
                $id = $rkey;
            }
        } else {
            if (is_numeric($rkey)) {
                $id = $rkey;
            } else {
                $this->redirect($rateit->homeLink);
                exit;
            }
        }

        $typ = Input::get('typ');

        // compose base options
        $options = array(
            'rkey' => $rkey,
            'typ'  => $typ,
        );

        $this->rateit->f_link = $this->createUrl(array('act' => 'view', 'rkey' => $rkey, 'typ' => $typ));

        if (isset($GLOBALS['TL_CONFIG']['rating_listsize']))
            $perpage = (int)trim($GLOBALS['TL_CONFIG']['rating_listsize']);
        if (! isset($perpage) || $perpage < 0) $perpage = 10;

        if ($rateit->f_page >= 0 && $perpage > 0) {
            $options['first'] = $rateit->f_page * $perpage;
            $options['limit'] = $perpage;
        } // if

        $rateit->ratingitems = $this->getRatingItems($options, true);
        if (count($rateit->ratingitems) < 1) $this->redirect($rateit->homeLink);
        $ext = &$rateit->ratingitems[0];

        $ext->ratings = $this->getRatings($ext, $options);
        if ($rateit->f_page >= 0 && $perpage > 0 && count($ext->ratings) == 0) {
            $rateit->f_page   = 0;
            $options['first'] = 0;
            $rateit->ratings  = $this->getRatings($ext, $options);
        } // if

        if (count($ext->ratings) > 0) {
            $totrecs = $ext->ratings[0]->totcount;
        } else {
            $totrecs = 0;
        }

        // create pages list
        $rateit->pages = array();
        if ($perpage > 0) {
            $first = 1;
            while ($totrecs > 0) {
                $cnt             = $totrecs > $perpage ? $perpage : $totrecs;
                $rateit->pages[] = $first . ' - ' . ($first + $cnt - 1);
                $first           += $cnt;
                $totrecs         -= $cnt;
            } // while
        } // if

        $ext->statistics       = $this->getRatingStatistics($ext->item_id);
        $ext->ratingsChartData = $this->getRatingsChartData($ext->statistics);
        $ext->monthsChartData  = $this->getMonthsChartData($ext->item_id);
    } // viewRating

    protected function resetRatings()
    {
        $rateit = &$this->Template->rateit;

        // nothing checked?
        $ids0 = Input::post('selectedids');
        if (! is_array($ids0)) {
            $this->redirect($rep->homeLink);
            return;
        }

        foreach ($ids0 as $id) {
            list($rkey, $typ) = explode('__', $id);
            $pid = $this->Database->prepare('SELECT id FROM tl_rateit_items WHERE rkey=? and typ=?')
                ->execute($rkey, $typ)
                ->fetchRow();
            $this->Database->prepare('DELETE FROM tl_rateit_ratings WHERE pid=?')
                ->execute($pid[0]);
        }

        $this->redirect($rateit->homeLink);

    } // resetRatings

    /**
     * Create url for hyperlink to the current page.
     * @param array $aParams Assiciative array with key/value pairs as parameters.
     * @return string The create link.
     */
    protected function createUrl($aParams = null)
    {
        return $this->createPageUrl(Input::get('do'), $aParams);
    } // createUrl

    /**
     * Create url for hyperlink to an arbitrary page.
     * @param string $aPage   The page ID.
     * @param array  $aParams Assiciative array with key/value pairs as parameters.
     * @return string The create link.
     */
    protected function createPageUrl($aPage, $aParams = null)
    {
        $url = 'contao?do=' . $aPage;
        if (is_array($aParams)) {
            foreach ($aParams as $key => $val)
                if ($val != '')
                    $url .= '&amp;' . $key . '=' . $val;
        }
        return $url;
    } // createPageUrl

    /**
     * Get post parameter and filter value.
     * @param string $aKey    The post key. When filtering html, remove all attribs and
     *                        keep the plain tags.
     * @param string $aMode   '': no filtering
     *                        'nohtml': strip all html
     *                        'text': Keep tags p br ul li em
     * @return string The filtered input.
     */
    protected function filterPost($aKey, $aMode = '')
    {
        $v = trim(Input::postRaw($aKey));
        if ($v == '' || $aMode == '') return $v;
        switch ($aMode) {
            case 'nohtml':
                $v = strip_tags($v);
                break;
            case 'text':
                $v = strip_tags($v);
                break;
        } // switch
        $v = preg_replace('/<(\w+) .*>/U', '<$1>', $v);
        return $v;
    } // filterPost

    protected function getRatingItems($aOptions, $noLimit = false)
    {
        $sql = "SELECT i.id as item_id,
				i.rkey AS rkey,
				i.title as title,
				i.typ as typ,
				i.createdat as createdat,
				i.active as active,
				IFNULL(AVG(r.rating),0) AS rating,
				COUNT( r.rating ) AS totalRatings
				FROM tl_rateit_items i
				LEFT OUTER JOIN tl_rateit_ratings r
				ON (i.id = r.pid)
				%w
				GROUP BY rkey, title, item_id, typ, createdat, active
				%o
				%l";

        $cntSql = "SELECT COUNT(*) FROM tl_rateit_items i %s";

        $where      = '';
        $firstWhere = true;
        $limit      = '';
        $order      = '';

        foreach ($aOptions as $k => $v) {
            if ($k == 'find') {
                if (! $firstWhere) {
                    $where .= " AND";
                }
                $where      .= " title like '%$v%'";
                $firstWhere = false;
            } else if ($k != 'order' && $k != 'limit' && $k != 'first') {
                if (! $firstWhere) {
                    $where .= " AND";
                }
                $where      .= " $k='$v'";
                $firstWhere = false;
            } else {
                if ($k == 'limit' && ! $noLimit) {
                    $cntRows = $v;
                } else if ($k == 'first' && ! $noLimit) {
                    $first = $v;
                }
            }
        }

        if (isset($cntRows) && isset($first)) {
            $limit = "LIMIT $first, $cntRows";
        }

        if (strlen($where) > 0) {
            $where = "WHERE " . $where;
        }

        if (isset($aOptions['order']) && ! empty($aOptions['order']))
            $order = "ORDER BY " . $aOptions['order'];

        $sql = str_replace('%o', $order, $sql);
        $sql = str_replace('%w', $where, $sql);
        $sql = str_replace('%l', $limit, $sql);

        $cntSql = str_replace('%s', $where, $cntSql);

        $count = $this->Database->query($cntSql)->fetchRow();

        $arrRatingItems = $this->Database->query($sql)->fetchAllAssoc();
        $arrReturn      = array();
        foreach ($arrRatingItems as $rating) {
            if ($rating['active'] != '1') $rating['active'] = '0';
            $rating['percent']  = $rating['rating'];
            $rating['rating']   = $this->percentToStars($rating['percent']);
            $rating['stars']    = $this->intStars;
            $rating['totcount'] = $count[0];
            $arrReturn[]        = (object)$rating;
        }
        return $arrReturn;
    } // getRatingItems

    protected function getRatings($ext, $options = array())
    {
        // Gesamtanzahl (für Paging wichtig) ermitteln
        $cntSql = "SELECT COUNT(*) FROM tl_rateit_ratings r WHERE r.pid=$ext->item_id";
        $count  = $this->Database->prepare($cntSql)
            ->execute()
            ->fetchRow();

        foreach ($options as $k => $v) {
            if ($k == 'limit') {
                $cntRows = $v;
            } else if ($k == 'first') {
                $first = $v;
            }
        }

        if (isset($cntRows) && isset($first)) {
            $limit = "LIMIT $first, $cntRows";
        }

        $sql = "SELECT id AS rating_id, session_id, memberid, rating, createdat
		FROM tl_rateit_ratings r
		WHERE r.pid=$ext->item_id
		ORDER BY createdat DESC
		%l";
        $sql = str_replace('%l', $limit, $sql);

        $arrRatings = $this->Database->prepare($sql)
            ->execute()
            ->fetchAllAssoc();
        $arrReturn  = array();
        foreach ($arrRatings as $rating) {
            $rating['percent']  = $rating['rating'];
            $rating['rating']   = $this->percentToStars($rating['percent']);
            $rating['stars']    = $this->intStars;
            $rating['totcount'] = $count[0];
            if ($rating['memberid'] != null) {
                $member           = $this->Database->prepare("SELECT firstname, lastname FROM tl_member WHERE id=?")
                    ->limit(1)
                    ->execute($rating['memberid'])
                    ->fetchAssoc();
                $rating['member'] = $member['firstname'] . " " . $member['lastname'];
            }
            $arrReturn[] = (object)$rating;
        }
        return $arrReturn;
    } // getRatings

    protected function getRatingStatistics($item_id)
    {
        $sql = "SELECT rating, count(*) as count
		FROM tl_rateit_ratings r
		WHERE r.pid=$item_id
		GROUP BY rating
		ORDER BY rating";

        $arrRatingStatistics = $this->Database->prepare($sql)
            ->execute()
            ->fetchAllAssoc();
        $arrReturn           = array();
        foreach ($arrRatingStatistics as $rating) {
            $rating['percent']             = $rating['rating'];
            $rating['rating']              = $this->percentToStars($rating['percent']);
            $arrReturn[$rating['percent']] = (object)$rating;
        }
        return $arrReturn;
    } // getRatings

    protected function getRatingsChartData($statistics)
    {
        $arr         = array();
        $arr['cols'] = array();
        $arr['rows'] = array();

        // Spalten anlegen
        $arr['cols'][] = array('id' => 'rating', 'label' => $GLOBALS['TL_LANG']['tl_rateit']['rating_chart_legend'][2], 'type' => 'string');
        $arr['cols'][] = array('id' => 'count', 'label' => $GLOBALS['TL_LANG']['tl_rateit']['rating_chart_legend'][3], 'type' => 'number');

        // Zeilen anlegen
        foreach ($statistics as $obj) {
            $arr['rows'][] = array('c' => array(array('v' => $obj->rating . ' ' . ($obj->rating == 1 ? $this->label : $this->labels)), array('v' => (int)$obj->count, 'f' => $obj->count . ' ' . $GLOBALS['TL_LANG']['tl_rateit']['vote'][$obj->count == 1 ? 0 : 1])));
        }
        return json_encode($arr);
    }

    protected function getMonthsChartData($item_id)
    {

        $sql = "SELECT count(*) AS anzahl, avg(rating) AS bewertung, month(date(FROM_UNIXTIME(createdat))) AS monat, year(date(FROM_UNIXTIME(createdat))) AS jahr
		FROM tl_rateit_ratings r
		WHERE r.pid=$item_id
		GROUP BY monat, jahr
		ORDER BY jahr DESC , monat DESC
		LIMIT 0 , 12";

        $arrResult = $this->Database->prepare($sql)
            ->execute()
            ->fetchAllAssoc();

        $arrResult = array_reverse($arrResult);

        $this->loadLanguageFile('default');

        $arr         = array();
        $arr['cols'] = array();
        $arr['rows'] = array();

        // Spalten anlegen
        $arr['cols'][] = array('id' => 'month', 'label' => $GLOBALS['TL_LANG']['tl_rateit']['month_chart_legend'][3], 'type' => 'string');
        $arr['cols'][] = array('id' => 'count', 'label' => $GLOBALS['TL_LANG']['tl_rateit']['month_chart_legend'][4], 'type' => 'number');
        $arr['cols'][] = array('id' => 'avg', 'label' => $GLOBALS['TL_LANG']['tl_rateit']['month_chart_legend'][2], 'type' => 'number');

        // Zeilen anlegen
        foreach ($arrResult as $result) {
            $month         = $GLOBALS['TL_LANG']['MONTHS'][$result['monat'] - 1] . ' ' . $result['jahr'];
            $avgValue      = round((float)(($result['bewertung'] * $this->intStars) / 100), 1);
            $arr['rows'][] = array('c' => array(array('v' => $month),
                array('v' => (int)$result['anzahl']),
                array('v' => $avgValue)));
        }
        return json_encode($arr);
    }

    protected function percentToStars($percent)
    {
        $modifier = 100 / $this->intStars;
        return round($percent / $modifier, 1);
    }

    /**
     * Convert encoding
     * @return String
     * @param $strString String to convert
     * @param string $from      charset to convert from
     * @param string $to        charset to convert to
     */
    public function convertEncoding($strString, $from, $to)
    {
        if (function_exists('mb_strlen')) {
            @mb_substitute_character('none');
            return @mb_convert_encoding($strString, $to, $from);
        } elseif (function_exists('iconv')) {
            if (strlen($iconv = @iconv($from, $to . '//IGNORE', $strString))) {
                return $iconv;
            } else {
                return @iconv($from, $to, $strString);
            }
        }
        return $strString;
    }
} // class rateitBackendModule
