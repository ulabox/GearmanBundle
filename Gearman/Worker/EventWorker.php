<?php

namespace Ulabox\Bundle\GearmanBundle\Gearman\Worker;

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
class EventWorker extends ContainerAwareWorker
{
    private function getUseVia()
    {
        return $this->container->getParameter('ulabox_gearman.use_via');
    }
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
        if ($this->getUseVia() == 'cli') {
            $this->cliDispatch($job);
        } else {
            $this->requestDispatch($job);
        }

        return true;
    }

    /**
     * @param \GearmanJob $job
     */
    private function cliDispatch(\GearmanJob $job)
    {
        try {
            $event = $this->unserializeEvent($job->workload());
            $this->container->get('event_dispatcher')->dispatch($event->getName(), $event);
            $result = array(
                'status'  => 'OK',
                'message' => $event->getName().' dispatched'
            );
        } catch (\Exception $e) {
            $this->container->get('logger')->critical('An error occurred during the worker asynchronous event handling.' . $e->getMessage() . PHP_EOL . 'stack trace: ' . $e->getTraceAsString());
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

    /**
     * @param \GearmanJob $job
     * @return bool
     */
    private function requestDispatch(\GearmanJob $job)
    {
        $response = $this->notify($job->workload());

        try {
            $response = json_decode($response, true);

            return $response['status'] == 'OK';
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Sending an asynchronous event from our PHP app
     *
     * @param string $data The serialize event
     *
     * @return string
     */
    private function notify($data)
    {
        $url = $this->getRouter()->generate('gearman_bundle_async_event_handle', array(), true);

        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($data));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        //execute post
        $result = curl_exec($ch);

        //close connection
        curl_close($ch);

        return $result;
    }

    /**
     * Get router.
     *
     * @return Router
     */
    private function getRouter()
    {
        return $this->container->get('router');
    }
}
