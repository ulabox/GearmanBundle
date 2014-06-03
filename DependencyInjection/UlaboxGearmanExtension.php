<?php

namespace Ulabox\Bundle\GearmanBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * Ulabox gearman extension.
 *
 * @author Ivannis Suárez Jérez <ivannis.suarez@gmail.com>
 */
class UlaboxGearmanExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $servers = array();
        if (count($config['servers']) > 0 ) {
            foreach ($config['servers'] as $serverName => $server) {
                $servers[] = $server['host'] . ($server['port'] ? ':'.$server['port'] : '');
            }
        } else {
            $servers[] = '127.0.0.1:4730';
        }

        $container->setParameter('ulabox_gearman.enable_asynchronous_event_dispatcher', $config['enable_asynchronous_event_dispatcher']);
        $container->setParameter('ulabox_gearman.enable_asynchronous_cli_event_dispatcher', $config['enable_asynchronous_cli_event_dispatcher']);
        $container->setParameter('ulabox_gearman.default_method', $config['default_method']);
        $container->setParameter('ulabox_gearman.client_dir', $config['client_dir']);
        $container->setParameter('ulabox_gearman.worker_dir', $config['worker_dir']);
        $container->setParameter('ulabox_gearman.iterations', $config['iterations']);
        $container->setParameter('ulabox_gearman.servers', $servers);
    }
}
