<?php

namespace Ulabox\Bundle\GearmanBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Ulabox\Bundle\GearmanBundle\DependencyInjection\Compiler\OverrideEventDispatcherCompilerPass;

/**
 * Gearman Job Server integration for symfony2.
 *
 * @author Ivannis Suárez Jérez <ivannis.suarez@gmail.com>
 */
class UlaboxGearmanBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new OverrideEventDispatcherCompilerPass());

        parent::build($container);
    }
}
