<?php

namespace Ulabox\Bundle\GearmanBundle\Dispatcher;

use Symfony\Component\EventDispatcher\Event;

/**
 * Interface for async event dispatcher.
 *
 * @author Ivannis Suárez Jérez <ivannis.suarez@gmail.com>
 */
interface EventDispatcherAsyncInterface
{
    /**
     * Dispatch and event asyn.
     *
     * @param string $eventName The event name
     * @param Event  $event     The event instance
     *
     * @return Event
     */
    public function dispatchAsync($eventName, Event $event = null);
}