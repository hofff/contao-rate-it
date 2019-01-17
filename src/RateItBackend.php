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

namespace Hofff\Contao\RateIt;

use Contao\StringUtil;

class RateItBackend
{
    const path = 'bundles/cgoitrateit/';

    /**
     * Get a css file.
     * @param string $file The basename if the file (without extension).
     * @return string The file path.
     */
    public static function css($file)
    {
        return self::path . 'css/' . $file . '.css';
    } // file

    /**
     * Get a js file.
     * @param string $file The basename if the file (without extension).
     * @return string The file path.
     */
    public static function js($file)
    {
        return self::path . 'js/' . $file . '.js';
    } // file

    /**
     * Get image url from the theme.
     * @param string $file The basename if the image (without extension).
     * @return string The image path.
     */
    public static function image($file)
    {
        $url = self::path . 'images/';
        if (is_file(TL_ROOT . '/' . $url . $file . '.png')) return $url . $file . '.png';
        if (is_file(TL_ROOT . '/' . $url . $file . '.gif')) return $url . $file . '.gif';
        return $url . 'default.png';
    } // image

    /**
     * Create a 'img' tag from theme icons.
     * @param string $file       The basename if the image (without extension).
     * @param string $alt        The 'alt' text.
     * @param string $attributes Additional tag attributes.
     * @return string The html code.
     */
    public static function createImage($file, $alt = '', $attributes = '')
    {
        if ($alt == '') $alt = 'icon';
        $img  = self::image($file);
        $size = getimagesize(TL_ROOT . '/' . $img);
        return '<img' . ((substr($img, -4) == '.png') ? ' class="pngfix"' : '') . ' src="' . $img . '" ' . $size[3] . ' alt="' . StringUtil::specialchars($alt) . '"' . (($attributes != '') ? ' ' . $attributes : '') . '>';
    } // createImage

    /**
     * Create a list button (link button)
     * @param string  $file    The basename if the image (without extension).
     * @param string  $link    The URL of the link to create.
     * @param string  $text    The alt/title text.
     * @param string  $confirm Optional confirmation text before redirecting to the link.
     * @param boolean $popup   Open the target in a new window.
     * @return string The html code.
     */
    public function createListButton($file, $link, $text, $confirm = '', $popup = false)
    {
        $target  = $popup ? ' target="_blank"' : '';
        $onclick = ($confirm != '') ? ' onclick="if(!confirm(\'' . $confirm . '\'))return false"' : '';
        return '<a href="' . $link . '" title="' . $text . '"' . $target . $onclick . '>' . $this->createImage($file, $text) . '</a>';
    } // createListButton

    public function createMainButton($file, $link, $text, $confirm = '')
    {
        $onclick = ($confirm == '')
            ? ''
            : ' onclick="if(!confirm(\'' . $confirm . '\'))return false"';
        return '<a href="' . $link . '" title="' . $text . '"' . $onclick . '>' . $this->createImage($file, $text) . ' ' . $text . '</a>';
    } // createMainButton
} // class RateItBackend
