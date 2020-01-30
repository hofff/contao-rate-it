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

namespace Hofff\Contao\RateIt\ContaoManager;

use Contao\CommentsBundle\ContaoCommentsBundle;
use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Routing\RoutingPluginInterface;
use Contao\NewsBundle\ContaoNewsBundle;
use Hofff\Contao\RateIt\HofffContaoRateItBundle;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouteCollection;

/**
 * Plugin for the Contao Manager.
 *
 * @author Carsten Götzinger
 */
final class Plugin implements BundlePluginInterface, RoutingPluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser) : array
    {
        return [
            BundleConfig::create(HofffContaoRateItBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class, ContaoNewsBundle::class, ContaoCommentsBundle::class])
                ->setReplace(['rate-it']),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteCollection(LoaderResolverInterface $resolver, KernelInterface $kernel): ?RouteCollection
    {
        return $resolver
            ->resolve(__DIR__ . '/../Resources/config/routing.xml')
            ->load(__DIR__ . '/../Resources/config/routing.xml');
    }
}
