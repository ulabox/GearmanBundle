<?php

namespace Ulabox\Bundle\GearmanBundle\Model;

/**
 * Interface for dependent worker.
 *
 * @author Ivannis Suárez Jérez <ivannis.suarez@gmail.com>
 */
interface DependentWorkerInterface
{
    /**
     * This method must return an array of workers classes
     * on which the implementing class depends on
     *
     * @return array
     */
    function getDependencies();
}