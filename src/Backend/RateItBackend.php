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

use Contao\StringUtil;
use Contao\System;

/** @psalm-suppress ClassMustBeFinal */
class RateItBackend
{
    private const string PATH = 'bundles/hofffcontaorateit/';

    /**
     * Get a css file.
     * @param string $file The basename if the file (without extension).
     * @return string The file path.
     */
    public static function css(string $file): string
    {
        return self::PATH . 'css/' . $file . '.css';
    }

    /**
     * Get a js file.
     * @param string $file The basename if the file (without extension).
     * @return string The file path.
     */
    public static function javascript(string $file): string
    {
        return self::PATH . 'js/' . $file . '.js';
    }

    /**
     * Get image url from the theme.
     * @param string $file The basename if the image (without extension).
     * @return string The image path.
     */
    public static function image(string $file): string
    {
        /** @psalm-suppress PossiblyInvalidCast */
        $webDirectory = (string) System::getContainer()->getParameter('contao.web_dir');
        $url          = self::PATH . 'images/';

        if (is_file($webDirectory . '/' . $url . $file . '.png')) {
            return $url . $file . '.png';
        }

        if (is_file($webDirectory . '/' . $url . $file . '.gif')) {
            return $url . $file . '.gif';
        }

        return $url . 'star.gif';
    }

    /**
     * Create an 'img' tag from theme icons.
     *
     * @param string $file       The basename if the image (without extension).
     * @param string $alt        The 'alt' text.
     * @param string $attributes Additional tag attributes.
     *
     * @return string The html code.
     */
    public static function createImage(string $file, string $alt = '', string $attributes = ''): string
    {
        if ($alt == '') {
            $alt = 'icon';
        }
        $img  = self::image($file);
        $size = getimagesize($img);

        return sprintf(
            '<img%s src="%s" alt="%s"%s>',
            $img,
            $size[3] ?? '',
            StringUtil::specialchars($alt),
            $attributes !== '' ? (' ' . $attributes) : '',
        );
    }

    /**
     * Create a list button (link button)
     *
     * @param string  $file    The basename if the image (without extension).
     * @param string  $link    The URL of the link to create.
     * @param string  $text    The alt/title text.
     * @param string  $confirm Optional confirmation text before redirecting to the link.
     * @param boolean $popup   Open the target in a new window.
     *
     * @return string The HTML code.
     */
    public function createListButton(
        string $file,
        string $link,
        string $text,
        string $confirm = '',
        bool $popup = false
    ): string {
        $target  = $popup ? ' target="_blank"' : '';
        $onclick = ($confirm != '') ? ' onclick="if(!confirm(\'' . $confirm . '\'))return false"' : '';

        return sprintf(
            '<a href="%s" title="%s"%s%s>%s</a>',
            $link,
            $text,
            $target,
            $onclick,
            static::createImage($file, $text),
        );
    }
}
