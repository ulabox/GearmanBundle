<?php

namespace Ulabox\Bundle\GearmanBundle\Model;

use Ulabox\Bundle\GearmanBundle\Manager\GearmanManager;

/**
 * Interface for gearman worker.
 *
 * @author Ivannis Suárez Jérez <ivannis.suarez@gmail.com>
 */
interface WorkerInterface
{
    /**
     * Configure worker
     */
    public function configure();

    /**
     * Set gearman manager
     *
     * @param GearmanManager $manager
     */
    public function setManager(GearmanManager $manager);
}