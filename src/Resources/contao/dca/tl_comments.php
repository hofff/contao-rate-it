<?php

/**
 * This file is part of hofff/contao-rate-it.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author     David Molineus <david@hofff.com>
 * @copyright  2019-2020 hofff.com.
 * @license    https://github.com/hofff/contao-rate-it/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

use Hofff\Contao\RateIt\EventListener\Dca\CommentsDcaListener;

$GLOBALS['TL_DCA']['tl_comments']['config']['onload_callback'][] = [CommentsDcaListener::class, 'onLoad'];
$GLOBALS['TL_DCA']['tl_comments']['config']['onundo_callback'][] = [CommentsDcaListener::class, 'onUndo'];
