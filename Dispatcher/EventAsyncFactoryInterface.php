<?php

namespace Ulabox\Bundle\GearmanBundle\Dispatcher;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;

/**
 * Interface for async event factory.
 *
 * @author Ivannis Suárez Jérez <ivannis.suarez@gmail.com>
 */
interface EventAsyncFactoryInterface
{
    /**
     * Reconstruct an async event received through the gearman queue.
     *
     * @param Request $request The request
     *
     * @return Event The original event
     */
    public function getReconstructedEvent(Request $request);
}