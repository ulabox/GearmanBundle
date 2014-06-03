<?php

namespace Ulabox\Bundle\GearmanBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * @author Ivannis Suárez Jérez <ivannis.suarez@gmail.com>
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
            ->scalarNode('enable_asynchronous_event_dispatcher')->defaultValue(false)->cannotBeEmpty()->end()
            ->scalarNode('use_via')->defaultValue('request')->cannotBeEmpty()->end()
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
