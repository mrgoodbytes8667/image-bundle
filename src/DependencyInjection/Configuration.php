<?php


namespace Bytes\ImageBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use function Symfony\Component\String\u;

class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('bytes_image');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('cache')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('local')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('success')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('key')
                                            ->defaultValue('bytes_image')
                                            ->validate()
                                                ->always()->then(function ($value) {
                                                    $key = u($value);
                                                    if($key->endsWith('.'))
                                                    {
                                                        $key = $key->beforeLast('.');
                                                    }
                                                    
                                                    return $key->toString();
                                                })
                                            ->end()
                                        ->end()
                                        ->integerNode('duration')
                                            ->min(1)
                                            ->defaultValue(15)
                                            ->info('Length of time (in minutes) to cache a remote image temporarily when instantiating the cache')
                                        ->end()
                                        ->booleanNode('enable')->defaultTrue()->info('Cache remote URL responses for a short time to prevent repeated calls to remote sites')->end()
                                    ->end()
                                ->end()
                                ->arrayNode('fallback')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('key')
                                            ->defaultValue('bytes_image')
                                            ->validate()
                                                ->always()->then(function ($value) {
                                                    $key = u($value);
                                                    if($key->endsWith('.'))
                                                    {
                                                        $key = $key->beforeLast('.');
                                                    }
                                                    
                                                    return $key->toString();
                                                })
                                            ->end()
                                        ->end()
                                        ->integerNode('duration')
                                            ->min(1)
                                            ->defaultValue(5)
                                            ->info('Length of time (in minutes) to cache a remote image temporarily when instantiating the cache')
                                        ->end()
                                        ->booleanNode('enable')->defaultTrue()->info('Cache remote URL responses for a short time to prevent repeated calls to remote sites')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end() // end local
                        ->arrayNode('response')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('success')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->arrayNode('cached')
                                            ->addDefaultsIfNotSet()
                                            ->children()
                                                ->integerNode('duration')
                                                    ->min(1)
                                                    ->defaultValue(15)
                                                    ->info('Length of time (in minutes) to tell the browser cache to cache for')
                                                ->end()
                                            ->end()
                                        ->end()

                                        ->arrayNode('initial')
                                            ->addDefaultsIfNotSet()
                                            ->children()
                                                ->integerNode('duration')
                                                    ->min(1)
                                                    ->defaultValue(15)
                                                    ->info('Length of time (in minutes) to tell the browser cache to cache for')
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                                ->arrayNode('fallback')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->integerNode('duration')
                                            ->min(1)
                                            ->defaultValue(5)
                                            ->info('Length of time (in minutes) to tell the browser cache to cache for')
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end() // end response

                    ->end()
                ->end() // end cache
            ->end();

        return $treeBuilder;
    }
}