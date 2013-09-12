<?php

namespace Ulabox\Bundle\GearmanBundle\Model;

use Ulabox\Bundle\GearmanBundle\Manager\GearmanManager;

/**
 * Default gearman worker,
 *
 * @author Ivannis Suárez Jérez <ivannis.suarez@gmail.com>
 */
class Worker implements WorkerInterface
{
    /**
     * The gearman manager
     *
     * @var GearmanManager
     */
    protected $manager;

    /**
     * {@inheritdoc}
     */
    public function setManager(GearmanManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function configure()
    {

    }
}