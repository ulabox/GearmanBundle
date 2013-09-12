<?php

namespace Ulabox\Bundle\GearmanBundle\Model;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * Container aware worker.
 *
 * @author Ivannis Suárez Jérez <ivannis.suarez@gmail.com>
 */
class ContainerAwareWorker extends Worker implements ContainerAwareInterface
{
    /**
     * The service container
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}