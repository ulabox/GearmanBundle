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
        $response = $this->notify($job->workload());

        try {
            $response = json_decode($response, true);

            return $response['status'] == 'OK';
        } catch (\Exception $e) {
            return false;
        }

        return true;
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
