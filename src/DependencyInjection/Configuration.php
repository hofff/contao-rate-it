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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use function func;
use function is_array;
use function var_dump;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder() : TreeBuilder
    {
        $treeBuilder = new TreeBuilder('hofff_contao_rate_it');
        $rootNode    = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('types')
                    ->info('Enable or disable ratings for different content types')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('page')
                            ->info('If enabled rating for pages can be configured')
                            ->defaultTrue()
                        ->end()
                        ->booleanNode('article')
                            ->info('If enabled rating for articles can be configured')
                            ->defaultTrue()
                        ->end()
                        ->booleanNode('news')
                            ->info('If enabled rating for new can be configured')
                            ->defaultTrue()
                        ->end()
                        ->booleanNode('module')
                            ->info('If enabled rating module can be configured')
                            ->defaultTrue()
                        ->end()
                        ->booleanNode('ce')
                            ->info('If enabled rating content element can be configured')
                            ->defaultTrue()
                        ->end()
                        ->booleanNode('comments')
                            ->info('If enabled rating for comments can be configured')
                            ->defaultTrue()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('comment_sources')
                    ->info('Defines in which source the rating is activated for comments')
                    ->scalarPrototype()
                    ->end()
                    ->beforeNormalization()
                        ->always()
                        ->then(
                            static function ($value) {
                                if (!is_array($value)) {
                                    return ['tl_news' => 'tl_news_archive'];
                                }

                                if (!isset($value['tl_news'])) {
                                    $value['tl_news'] = 'tl_news_archive';
                                }

                                return $value;
                            }
                        )
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
