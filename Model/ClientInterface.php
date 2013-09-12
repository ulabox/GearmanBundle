<?php

namespace Ulabox\Bundle\GearmanBundle\Model;

/**
 * Interface for gearman client.
 *
 * @author Ivannis Suárez Jérez <ivannis.suarez@gmail.com>
 */
interface ClientInterface
{
    /**
     * Configure client
     */
    public function configure();
}