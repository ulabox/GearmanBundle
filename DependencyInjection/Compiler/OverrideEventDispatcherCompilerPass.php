<?php

namespace Ulabox\Bundle\GearmanBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Alias;

/**
 * Compiler pass that override the default event dispatcher.
 *
 * @author Ivannis Suárez Jérez <ivannis.suarez@gmail.com>
 */
class OverrideEventDispatcherCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->getParameterBag()->get('ulabox_gearman.enable_asynchronous_event_dispatcher')) {
            $container->setDefinition('event_dispatcher', $container->getDefinition('ulabox_gearman.event_dispatcher_async'));
            $container->removeDefinition('ulabox_gearman.event_dispatcher_async');
        }
    }
}