<?php

namespace Ulabox\Bundle\GearmanBundle\Gearman\Worker;

use Ulabox\Bundle\GearmanBundle\Dispatcher\EventAsyncFactoryInterface;
use Ulabox\Bundle\GearmanBundle\Model\ContainerAwareWorker;
use Ulabox\Bundle\GearmanBundle\Annotation\Worker;
use Ulabox\Bundle\GearmanBundle\Annotation\Job;

/**
 * Event worker.
 *
 * @Worker()
 *
 * @author Ivannis Suárez Jérez <ivannis.suarez@gmail.com>
 */
class CliEventWorker extends ContainerAwareWorker
{
    /**
     * Execute the event job.
     *
     * @param \GearmanJob $job The GearmanJob instance
     *
     * @return boolean
     *
     * @Job()
     */
    public function dispatch(\GearmanJob $job)
    {
        try {
            $event = $this->unserializeEvent($job->workload());
            $this->container->get('event_dispatcher')->dispatch($event->getName(), $event);
            $result = array(
                'status'  => 'OK',
                'message' => $event->getName().' dispatched'
            );
        } catch (\Exception $e) {
            $this->getContainer()->get('logger')->critical('An error occurred during the worker asynchronous event handling.' . $e->getMessage() . PHP_EOL . 'stack trace: ' . $e->getTraceAsString());
            $result = array(
                'status'  => 'FAILED',
                'message' => $e->getMessage()
            );
        }
        echo json_encode($result);
    }

    /**
     * Get the async event factory
     *
     * @return EventAsyncFactoryInterface
     */
    private function unserializeEvent($eventData)
    {
        // deserialize and reconstructs the original event
        list($eventClass, $eventName, $eventArguments) = unserialize($eventData);

        $class = new \ReflectionClass($eventClass);

        $event = $class->newInstanceWithoutConstructor();
        $event->setName($eventName);
        $event->setArguments($eventArguments);

        return $event;
    }
}
