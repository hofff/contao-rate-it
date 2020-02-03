<?php

/**
 * This file is part of hofff/contao-rate-it.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author     David Molineus <david@hofff.com>
 * @copyright  2019 hofff.com.
 * @license    https://github.com/hofff/contao-rate-it/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace Hofff\Contao\RateIt\DependencyInjection;

use Hofff\Contao\RateIt\EventListener\Hook\RateItArticleListener;
use Hofff\Contao\RateIt\EventListener\Hook\RateItCommentsListener;
use Hofff\Contao\RateIt\EventListener\Hook\RateItNewsListener;
use Hofff\Contao\RateIt\EventListener\Hook\RateItPageListener;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use function in_array;
use function var_dump;

final class HofffContaoRateItExtension extends Extension
{
    /** @param mixed[][] $configs */
    public function load(array $configs, ContainerBuilder $container) : void
    {
        $loader = new XmlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $loader->load('services.xml');
        $loader->load('listeners.xml');

        $config  = $this->processConfiguration(new Configuration(), $configs);
        $types   = array_keys(array_filter($config['types']));
        $bundles = $container->getParameter('kernel.bundles');

        $container->setParameter('hofff.contao_rate_it.types', $types);
        $container->setParameter('hofff.contao_rate_it.comment_sources', $config['comment_sources']);

        if (! in_array('page', $types, true)) {
            $container->removeDefinition(RateItPageListener::class);
        }

        if (! in_array('article', $types, true)) {
            $container->removeDefinition(RateItArticleListener::class);
        }

        if (! isset($bundles['ContaoNewsBundle']) || ! in_array('news', $types, true)) {
            $container->removeDefinition(RateItNewsListener::class);
        }

        if (! isset($bundles['ContaoCommentsBundle']) || ! in_array('comments', $types, true)) {
            $container->removeDefinition(RateItCommentsListener::class);
        }
    }
}
