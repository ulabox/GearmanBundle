<?php

namespace Ulabox\Bundle\GearmanBundle\Model;

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