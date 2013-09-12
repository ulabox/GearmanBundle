<?php

namespace Ulabox\Bundle\GearmanBundle\Dispatcher;

use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Symfony\Component\EventDispatcher\Event;

/**
 * Async event dispatcher.
 *
 * @author Ivannis Suárez Jérez <ivannis.suarez@gmail.com>
 */
class EventDispatcherAsync extends ContainerAwareEventDispatcher implements EventDispatcherAsyncInterface
{
    /**
     * {@inheritdoc}
     */
    public function dispatchAsync($eventName, Event $event = null)
    {
        if (null === $event) {
            $event = new Event();
        }

        $event->setDispatcher($this);
        $event->setName($eventName);

        if (!$this->hasListeners($eventName)) {
            return $event;
        }

        $this->doDispatchAsync($event);

        return $event;
    }

    /**
     * Sent an event to gearman queue.
     *
     * @param Event $event The event object to pass to the event handlers/listeners.
     */
    protected function doDispatchAsync(Event $event)
    {
        $eventAsync = $this->getContainer()->get('ulabox_gearman.event_async');
        $eventAsync->forward($event);
    }
}