<?php

namespace Ulabox\Bundle\GearmanBundle\Model;

/**
 * Interface for ordered worker.
 *
 * @author Ivannis Suárez Jérez <ivannis.suarez@gmail.com>
 */
interface OrderedWorkerInterface
{
    /**
     * Get the order of this worker
     *
     * @return integer
     */
    function getOrder();
}