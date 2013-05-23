<?php

namespace Ulabox\Bundle\GearmanBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ulabox_gearman');

        $rootNode
        ->children()
            ->scalarNode('default_method')->defaultValue('doBackgroundJob')->cannotBeEmpty()->end()
            ->scalarNode('client_dir')->defaultValue('Gearman/Client')->cannotBeEmpty()->end()
            ->scalarNode('worker_dir')->defaultValue('Gearman/Worker')->cannotBeEmpty()->end()
            ->scalarNode('iterations')->defaultValue(100)->cannotBeEmpty()->end()
            ->arrayNode('servers')
                ->info('Defines the gearman servers')
                ->useAttributeAsKey('name')
                ->requiresAtLeastOneElement()
                ->prototype('array')
                    ->children()
                        ->scalarNode('host')->end()
                        ->scalarNode('port')->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
