<?php

namespace Ulabox\Bundle\GearmanBundle\Dispatcher;

use Symfony\Component\EventDispatcher\Event;

/**
 * Interface for event async.
 *
 * @author Ivannis Suárez Jérez <ivannis.suarez@gmail.com>
 */
interface EventAsyncInterface
{
    /**
     * Sent and event to the gearman queue.
     *
     * @param Event $event The event instance
     */
    public function forward(Event $event);
}