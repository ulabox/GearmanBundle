<?php

namespace Ulabox\Bundle\GearmanBundle\Dispatcher;

use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class CliEventDispatcherAsync
 * @package Ulabox\Bundle\GearmanBundle\Dispatcher
 */
class CliEventDispatcherAsync extends ContainerAwareEventDispatcher implements EventDispatcherAsyncInterface
{
    const GEARMAN_SERVICE_CLASS_ALIAS = 'ulabox_gearman.manager';
    const GEARMAN_CLIENT_CLASS_ALIAS = 'UlaboxGearmanBundle:GearmanClient';
    const GEARMAN_WORKER_CLASS_ALIAS = 'UlaboxGearmanBundle:CliEventWorker';

    private $manager;

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

    private $gearman_manager = null;
    private function getGearmanManager()
    {
        if (is_null($this->gearman_manager)) {
            $this->gearman_manager = $this->getContainer()->get(self::GEARMAN_SERVICE_CLASS_ALIAS);
        }
        return $this->gearman_manager;
    }

    private function getGearmanClient()
    {
        return $this->getGearmanManager()->getClient(self::GEARMAN_CLIENT_CLASS_ALIAS);
    }

    private function getGearmanWorker()
    {
        return $this->getGearmanManager()->getWorker(self::GEARMAN_WORKER_CLASS_ALIAS);
    }

    /**
     * Sent an event to gearman queue.
     *
     * @param Event $event The event object to pass to the event handlers/listeners.
     */
    protected function doDispatchAsync(Event $event)
    {

        /**
         * @var \GearmanClient $gearmanClient
         */
        $gearmanClient = $this->getGearmanClient();

        /**
         * @var \GearmanWorker $gearmanWorker
         */
        $gearmanWorker = $this->getGearmanWorker();

        // now you should tell the client that worker must be run
        $gearmanClient->setWorker($gearmanWorker);

        $serializeEvent = serialize(array(get_class($event), $event->getName(), $event->getArguments()));

        try {
            // do the job in background
            $gearmanClient->doBackgroundJob('dispatch', $serializeEvent);
        } catch (\Exception $e) {
            $this->getContainer()->get('logger')->critical('An error occurred during the asynchronous event dispatching.' . $e->getMessage() . PHP_EOL . 'stack trace: ' . $e->getTraceAsString());
        }
    }
}