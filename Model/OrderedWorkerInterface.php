<?php

namespace Ulabox\Bundle\GearmanBundle\Model;

interface OrderedWorkerInterface
{
    /**
     * Get the order of this worker
     *
     * @return integer
     */
    function getOrder();
}