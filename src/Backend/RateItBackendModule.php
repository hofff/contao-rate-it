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

namespace Hofff\Contao\RateIt\Backend;

use Contao\BackendModule;
use Contao\BackendUser;
use Contao\Config;
use Contao\DataContainer;
use Contao\Input;
use Contao\System;
use Contao\Template;
use Hofff\Contao\RateIt\Rating\RatingTypes;
use stdClass;

use function is_array;

use const JSON_THROW_ON_ERROR;

/**
 * @property BackendUser $BackendUser
 * @property Template $Template
 * @SuppressWarnings(PHPMD.LongVariable)
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class RateItBackendModule extends BackendModule
{
    protected       $strTemplate;

    protected array $actions = [
        //	  act[0]			strTemplate					compiler
        ['', 'rateitbe_ratinglist', 'listRatings'],
        ['reset_ratings', '', 'resetRatings'],
        ['view', 'rateitbe_ratingview', 'viewRating'],
    ];

    protected stdClass $rateit;

    private string $compiler;
    private string $action = '';
    private string $parameter = '';

    private array $exportHeader;
    private array $exportHeaderDetails;

    /** Anzahl der Herzen/Sterne */
    protected int $intStars = 5;

    protected string $label;
    protected string $labels;

    /**
     * Initialize the controller
     */
    public function __construct(DataContainer|null $dataContainer = null)
    {
        parent::__construct($dataContainer);

        $this->import(BackendUser::class, 'BackendUser');

        $this->label  = $GLOBALS['TL_LANG']['rateit']['star'];
        $this->labels = $GLOBALS['TL_LANG']['rateit']['stars'];

        $this->loadLanguageFile('rateit_backend');
        $this->exportHeader        = &$GLOBALS['TL_LANG']['tl_rateit']['xls_headers'];
        $this->exportHeaderDetails = &$GLOBALS['TL_LANG']['tl_rateit']['xls_headers_detail'];
    }

    /**
     * Generate module:
     * - Display a wildcard in the back end
     * - Select the template and compiler in the front end
     * @return string
     */
    #[\Override]
    public function generate(): string
    {
        $this->rateit           = new \stdClass();
        $this->rateit->username = $this->BackendUser->username;
        $this->rateit->isadmin  = $this->BackendUser->isAdmin;

        $this->strTemplate = $this->actions[0][1];
        $this->compiler    = $this->actions[0][2];

        $act = Input::get('act') ?: Input::post('act');

        foreach ($this->actions as $action) {
            if ($act === $action[0]) {
                /** @psalm-suppress PossiblyInvalidCast */
                $this->parameter   = (string) $act;
                $this->action      = $action[0];
                $this->strTemplate = $action[1];
                $this->compiler    = $action[2];
                break;
            }
        }

        $stars = (int) Config::get('rating_count');
        if ($stars > 0) {
            $this->intStars = $stars;
        }

        return str_replace(['{{', '}}'], ['[{]', '[}]'], parent::generate());
    }

    /**
     * Compile module: common initializations and forwarding to distinct function compiler
     */
    #[\Override]
    protected function compile(): void
    {
        // hide module?
        $compiler = $this->compiler;
        if ($compiler === 'hide') {
            return;
        }

        $this->Template->rateit = $this->rateit;

        // complete rateit initialization
        $rateit           = $this->rateit;
        $rateit->f_link   = $this->createUrl([$this->action => $this->parameter]);
        $rateit->f_action = $this->compiler;
        $rateit->f_mode   = $this->action;
        $rateit->theme    = new RateItBackend();
        $rateit->backLink = $this->getReferer(true);
        $rateit->homeLink = $this->createUrl();

        $this->$compiler($this->parameter);
    }

    /**
     * List the ratings
     */
    protected function listRatings(): void
    {
        $rateit         = $this->Template->rateit;
        $rateit->f_page = 0;

        // returning from submit?
        if ($this->filterPost('rateit_action') == $rateit->f_action) {
            // get url parameters
            /** @psalm-suppress PossiblyInvalidCast */
            $rateit->f_typ          = trim((string) Input::post('rateit_typ'));
            /** @psalm-suppress PossiblyInvalidCast */
            $rateit->f_active       = trim((string) Input::post('rateit_active'));
            /** @psalm-suppress PossiblyInvalidCast */
            $rateit->f_parentstatus = trim((string) Input::post('rateit_parentstatus'));
            /** @psalm-suppress PossiblyInvalidCast */
            $rateit->f_order        = trim((string) Input::post('rateit_order'));
            /** @psalm-suppress PossiblyInvalidCast */
            $rateit->f_page         = trim((string) Input::post('rateit_page'));
            /** @psalm-suppress PossiblyInvalidCast */
            $rateit->f_find         = trim((string) Input::post('rateit_find'));
            $this->Session->set(
                'rateit_settings',
                ['rateit_typ'          => $rateit->f_typ, 'rateit_parentstatus' => $rateit->f_parentstatus, 'rateit_order'        => $rateit->f_order, 'rateit_page'         => $rateit->f_page, 'rateit_find'         => $rateit->f_find]
            );
        } else {
            $stg = $this->Session->get('rateit_settings');
            if (is_array($stg)) {
                $rateit->f_typ          = trim((string) $stg['rateit_typ']);
                $rateit->f_active       = trim((string) $stg['rateit_active']);
                $rateit->f_parentstatus = trim((string) $stg['rateit_parentstatus']);
                $rateit->f_order        = trim((string) $stg['rateit_order']);
                $rateit->f_page         = trim((string) $stg['rateit_page']);
                $rateit->f_find         = trim((string) $stg['rateit_find']);
            } // if
        } // if

        if ($rateit->f_order == '') $rateit->f_order = 'rating';
        //if (!isset($rateit->f_active)) $rateit->f_active = '-1';

        if (isset($GLOBALS['TL_CONFIG']['rating_listsize']))
            $perpage = (int)trim((string) $GLOBALS['TL_CONFIG']['rating_listsize']);
        if (! isset($perpage) || $perpage < 0) $perpage = 10;

        $options = [];
        if ($rateit->f_page >= 0 && $perpage > 0) {
            $options['first'] = (int) $rateit->f_page * $perpage;
            $options['limit'] = $perpage;
        } // if
        if ($rateit->f_typ != '') $options['typ'] = $rateit->f_typ;
        if ($rateit->f_active != '') $options['active'] = $rateit->f_active == '0' ? '' : $rateit->f_active;
        if ($rateit->f_parentstatus != '') $options['parentstatus'] = $rateit->f_parentstatus;
        if ($rateit->f_find != '') $options['find'] = $rateit->f_find;

        $options['order'] = match ($rateit->f_order) {
            'title' => 'title',
            'typ' => 'typ',
            'createdat' => 'createdat',
            default => 'rating desc'
        };

        // query extensions
        $rateit->ratingitems = $this->getRatingItems($options);
        if ($rateit->f_page >= 0 && $perpage > 0 && count($rateit->ratingitems) == 0) {
            $rateit->f_page      = 0;
            $options['first']    = 0;
            $rateit->ratingitems = $this->getRatingItems($options);
        } // if

        $totrecs = 0;
        // add view links
        foreach ($rateit->ratingitems as &$ext) {
            $ext->viewLink = $this->createUrl(['act' => 'view', 'rkey' => $ext->rkey, 'typ' => $ext->typ]);
            $totrecs       = $ext->totcount;
        } // foreach

        // create pages list
        $rateit->pages = [];
        if ($perpage > 0) {
            $first = 1;
            while ($totrecs > 0) {
                $cnt             = $totrecs > $perpage ? $perpage : $totrecs;
                $rateit->pages[] = $first . ' - ' . ($first + $cnt - 1);
                $first           += $cnt;
                $totrecs         -= $cnt;
            } // while
        } // if

        $this->Template->types = $this->getUsedTypes();
    } // listRatings

    /**
     * Detailed view of one rating.
     */
    protected function viewRating(): void
    {
        $rateit = $this->Template->rateit;

        $rateit->f_page = 0;

        // returning from submit?
        if ($this->filterPost('rateit_action') == $rateit->f_action) {
            // get url parameters
            /** @psalm-suppress PossiblyInvalidCast */
            $rateit->f_page = trim((string) Input::post('rateit_details_page'));
            $this->Session->set(
                'rateit_settings',
                ['rateit_details_page' => $rateit->f_page]
            );
        } else {
            $stg = $this->Session->get('rateit_settings');
            if (is_array($stg)) {
                $rateit->f_page = trim((string) $stg['rateit_details_page']);
            } // if
        } // if

        /** @psalm-suppress PossiblyInvalidCast */
        $rkey = (string) Input::get('rkey');
        if (strstr($rkey, '|')) {
            $arrRkey = explode('|', $rkey);
            foreach ($arrRkey as $key) {
                if (! is_numeric($key)) {
                    $this->redirect($rateit->homeLink);
                    exit;
                }
            }
        } else {
            if (! is_numeric($rkey)) {
                $this->redirect($rateit->homeLink);
                exit;
            }
        }

        $typ = Input::get('typ');

        // compose base options
        $options = ['rkey' => $rkey, 'typ'  => $typ];

        $this->rateit->f_link = $this->createUrl(['act' => 'view', 'rkey' => $rkey, 'typ' => $typ]);

        if (isset($GLOBALS['TL_CONFIG']['rating_listsize']))
            $perpage = (int)trim((string) $GLOBALS['TL_CONFIG']['rating_listsize']);
        if (! isset($perpage) || $perpage < 0) $perpage = 10;

        if ($rateit->f_page >= 0 && $perpage > 0) {
            $options['first'] = ((int) $rateit->f_page) * $perpage;
            $options['limit'] = $perpage;
        } // if

        $rateit->ratingitems = $this->getRatingItems($options, true);
        if (count($rateit->ratingitems) < 1) $this->redirect($rateit->homeLink);
        $ext = $rateit->ratingitems[0];

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
        $rateit->pages = [];
        if ($perpage > 0) {
            $first = 1;
            while ($totrecs > 0) {
                $cnt             = $totrecs > $perpage ? $perpage : $totrecs;
                $rateit->pages[] = $first . ' - ' . ($first + $cnt - 1);
                $first           += $cnt;
                $totrecs         -= $cnt;
            } // while
        } // if

        $ext->statistics       = $this->getRatingStatistics((int) $ext->item_id);
        $ext->ratingsChartData = $this->getRatingsChartData($ext->statistics);
        $ext->monthsChartData  = $this->getMonthsChartData($ext->item_id);
    } // viewRating

    protected function resetRatings(): void
    {
        if (Input::post('rateit_action') === 'updateinformation') {
            $this->updateParentInformation();

            return;
        }

        $rateit = $this->Template->rateit;

        // nothing checked?
        $ids0 = Input::post('selectedids');
        if (! is_array($ids0)) {
            $this->redirect($rateit->homeLink);

            return;
        }

        $removeParent = Input::post('rateit_action') == 'removeratings';

        foreach ($ids0 as $id) {
            [$rkey, $typ] = explode('__', (string) $id);
            $this->Database->beginTransaction();

            /** @psalm-suppress TooManyArguments */
            $pid = $this->Database->prepare('SELECT id FROM tl_rateit_items WHERE rkey=? and typ=?')
                ->execute($rkey, $typ)
                ->fetchRow();

            if ($pid === false) {
                continue;
            }

            /** @psalm-suppress TooManyArguments */
            $this->Database->prepare('DELETE FROM tl_rateit_ratings WHERE pid=?')
                ->execute($pid[0]);

            if ($removeParent) {
                /** @psalm-suppress TooManyArguments */
                $this->Database->prepare('DELETE FROM tl_rateit_items WHERE id=?')
                    ->execute($pid[0]);
            }

            $this->Database->commitTransaction();
        }

        $this->redirect($rateit->homeLink);

    } // resetRatings

    public function updateParentInformation(): void
    {
        $rateit = $this->Template->rateit;

        // nothing checked?
        $ids0 = Input::post('selectedids');
        if (! is_array($ids0)) {
            self::redirect($rateit->homeLink);
            return;
        }

        $pageTypes = self::getContainer()->get(RatingTypes::class);
        /** @psalm-suppress TooManyArguments */
        $result = $this->Database->execute('SELECT id, rkey, typ FROM tl_rateit_items');

        while ($result->next()) {
            $information = $pageTypes->sourceInformation($result->typ, (int) $result->rkey);
            if ($information === null) {
                /** @psalm-suppress TooManyArguments */
                $this->Database
                    ->prepare('UPDATE tl_rateit_items %s WHERE id=?')
                    ->set(['parentstatus' => 'r'])
                    ->execute($result->id);

                continue;
            }

            /** @psalm-suppress TooManyArguments */
            $this->Database
                ->prepare('UPDATE tl_rateit_items %s WHERE id=?')
                ->set(['parentstatus' => $information->parentStatus(), 'title' => $information->title()])
                ->execute($result->id);
        }

        self::redirect($rateit->homeLink);
    }

    /**
     * Create url for hyperlink to the current page.
     *
     * @param array $params Associative array with key/value pairs as parameters.
     *
     * @return string The create link.
     */
    protected function createUrl(array $params = []): string
    {
        /** @psalm-suppress PossiblyInvalidCast */
        return $this->createBackendModuleUrl((string) Input::get('do'), $params);
    }

    /**
     * Create url for hyperlink to an arbitrary page.
     *
     * @param string $module The backend module ID.
     * @param array  $params Associative array with key/value pairs as parameters.
     *
     * @return string The create link.
     */
    protected function createBackendModuleUrl(string $module, array $params = []): string
    {
        $params['do'] = $module;

        return System::getContainer()->get('router')->generate('contao_backend', $params);
    }

    /**
     * Get the post-parameter and filter the value.
     *
     * @param string $aKey    The post-key. When filtering html, remove all attribs and
     *                        keep the plain tags.
     * @param string $aMode   '': no filtering
     *                        'nohtml': strip all html
     *                        'text': Keep tags p br ul li em
     *
     * @return mixed The filtered input.
     */
    protected function filterPost(string $aKey, string $aMode = ''): mixed
    {
        /** @psalm-suppress PossiblyInvalidCast $value */
        $value = trim((string) Input::postRaw($aKey));
        if ($value == '' || $aMode == '') {
            return $value;
        }

        switch ($aMode) {
            case 'text':
            case 'nohtml':
                $value = strip_tags($value);
                break;
        }

        return (string) preg_replace('/<(\w+) .*>/U', '<$1>', $value);
    }

    protected function getRatingItems(array $options, bool $noLimit = false): array
    {
        $sql = "SELECT i.id as item_id,
				i.rkey AS rkey,
				i.title as title,
				i.typ as typ,
				i.createdat as createdat,
				i.active as active,
                i.parentstatus as parentstatus,
				IFNULL(AVG(r.rating),0) AS rating,
				COUNT( r.rating ) AS totalRatings
				FROM tl_rateit_items i
				LEFT OUTER JOIN tl_rateit_ratings r
				ON (i.id = r.pid)
				%w
				GROUP BY rkey, title, item_id, typ, createdat, active, parentstatus
				%o
				%l";

        $cntSql = "SELECT COUNT(*) FROM tl_rateit_items i %s";

        $where      = '';
        $firstWhere = true;
        $limit      = '';
        $order      = '';

        foreach ($options as $k => $v) {
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

        if (isset($options['order']) && ! empty($options['order']))
            $order = "ORDER BY " . $options['order'];

        $sql = str_replace('%o', $order, $sql);
        $sql = str_replace('%w', $where, $sql);
        $sql = str_replace('%l', $limit, $sql);

        $cntSql = str_replace('%s', $where, $cntSql);
        $count  = (int) $this->Database->query($cntSql)->fetchField();

        $arrRatingItems = $this->Database->query($sql)->fetchAllAssoc();
        $arrReturn      = [];
        foreach ($arrRatingItems as $rating) {
            if ($rating['active'] != '1') $rating['active'] = '0';
            $rating['percent']  = $rating['rating'];
            $rating['rating']   = $this->percentToStars((float) $rating['percent']);
            $rating['stars']    = $this->intStars;
            $rating['totcount'] = $count;
            $arrReturn[]        = (object)$rating;
        }
        return $arrReturn;
    } // getRatingItems

    protected function getRatings(stdClass $ext, array $options = []): array
    {
        // Gesamtanzahl (für Paging wichtig) ermitteln
        $cntSql = "SELECT COUNT(*) FROM tl_rateit_ratings r WHERE r.pid=$ext->item_id";
        $count  = (int) $this->Database->prepare($cntSql)
            ->execute()
            ->fetchField();

        foreach ($options as $k => $v) {
            if ($k == 'limit') {
                $cntRows = $v;
            } else if ($k == 'first') {
                $first = $v;
            }
        }

        $sql = "SELECT id AS rating_id, session_id, memberid, rating, createdat
		FROM tl_rateit_ratings r
		WHERE r.pid=$ext->item_id
		ORDER BY createdat DESC";

        if (isset($cntRows) && isset($first)) {
            $sql .= "\n LIMIT $first, $cntRows";
        }

        $arrRatings = $this->Database->prepare($sql)
            ->execute()
            ->fetchAllAssoc();
        $arrReturn  = [];
        foreach ($arrRatings as $rating) {
            $rating['percent']  = $rating['rating'];
            $rating['rating']   = $this->percentToStars((float) $rating['percent']);
            $rating['stars']    = $this->intStars;
            $rating['totcount'] = $count;
            if ($rating['memberid'] != null) {
                /** @psalm-suppress TooManyArguments */
                $member           = $this->Database->prepare("SELECT firstname, lastname FROM tl_member WHERE id=?")
                    ->limit(1)
                    ->execute($rating['memberid'])
                    ->fetchAssoc();
                $rating['member'] = $member
                    ? ($member['firstname'] . " " . $member['lastname'])
                    : 'ID ' . $rating['memberid'];
            }
            $arrReturn[] = (object)$rating;
        }
        return $arrReturn;
    } // getRatings

    protected function getRatingStatistics(int $itemId): array
    {
        $sql = "SELECT rating, count(*) as count
		FROM tl_rateit_ratings r
		WHERE r.pid=?
		GROUP BY rating
		ORDER BY rating";

        /** @psalm-suppress TooManyArguments */
        $arrRatingStatistics = $this->Database->prepare($sql)
            ->execute($itemId)
            ->fetchAllAssoc();
        $arrReturn           = [];
        foreach ($arrRatingStatistics as $rating) {
            $rating['percent']             = $rating['rating'];
            $rating['rating']              = $this->percentToStars((float) $rating['percent']);
            $arrReturn[$rating['percent']] = (object)$rating;
        }
        return $arrReturn;
    } // getRatings

    protected function getRatingsChartData(array $statistics): string
    {
        $arr         = [];
        $arr['cols'] = [];
        $arr['rows'] = [];

        // Spalten anlegen
        $arr['cols'][] = ['id' => 'rating', 'label' => $GLOBALS['TL_LANG']['tl_rateit']['rating_chart_legend'][2], 'type' => 'string'];
        $arr['cols'][] = ['id' => 'count', 'label' => $GLOBALS['TL_LANG']['tl_rateit']['rating_chart_legend'][3], 'type' => 'number'];

        // Zeilen anlegen
        foreach ($statistics as $obj) {
            $arr['rows'][] = [
                'c' => [
                    ['v' => $obj->rating . ' ' . ($obj->rating == 1 ? $this->label : $this->labels)],
                    ['v' => (int)$obj->count, 'f' => $obj->count . ' ' . $GLOBALS['TL_LANG']['tl_rateit']['vote'][$obj->count == 1 ? 0 : 1]]
                ]
            ];
        }

        return json_encode($arr, JSON_THROW_ON_ERROR);
    }

    protected function getMonthsChartData(int $itemId): string
    {

        $sql = "SELECT count(*) AS anzahl, avg(rating) AS bewertung, month(date(FROM_UNIXTIME(createdat))) AS monat, year(date(FROM_UNIXTIME(createdat))) AS jahr
		FROM tl_rateit_ratings r
		WHERE r.pid=$itemId
		GROUP BY monat, jahr
		ORDER BY jahr DESC , monat DESC
		LIMIT 0 , 12";

        $arrResult = $this->Database->prepare($sql)
            ->execute()
            ->fetchAllAssoc();

        $arrResult = array_reverse($arrResult);

        $this->loadLanguageFile('default');

        $arr         = [];
        $arr['cols'] = [];
        $arr['rows'] = [];

        // Spalten anlegen
        $arr['cols'][] = ['id' => 'month', 'label' => $GLOBALS['TL_LANG']['tl_rateit']['month_chart_legend'][3], 'type' => 'string'];
        $arr['cols'][] = ['id' => 'count', 'label' => $GLOBALS['TL_LANG']['tl_rateit']['month_chart_legend'][4], 'type' => 'number'];
        $arr['cols'][] = ['id' => 'avg', 'label' => $GLOBALS['TL_LANG']['tl_rateit']['month_chart_legend'][2], 'type' => 'number'];

        // Zeilen anlegen
        foreach ($arrResult as $result) {
            $month         = $GLOBALS['TL_LANG']['MONTHS'][$result['monat'] - 1] . ' ' . $result['jahr'];
            $avgValue      = round((float)(($result['bewertung'] * $this->intStars) / 100), 1);
            $arr['rows'][] = ['c' => [['v' => $month], ['v' => (int)$result['anzahl']], ['v' => $avgValue]]];
        }
        return json_encode($arr, JSON_THROW_ON_ERROR);
    }

    protected function percentToStars(float $percent): float
    {
        $modifier = (float) (100 / $this->intStars);
        return round($percent / $modifier, 1);
    }

    private function getUsedTypes() : array
    {
        return $this->Database->execute('SELECT typ FROM tl_rateit_items GROUP BY typ ORDER BY typ')->fetchEach('typ');
    }
}
