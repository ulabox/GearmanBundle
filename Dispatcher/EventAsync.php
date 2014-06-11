<?php

namespace Ulabox\Bundle\GearmanBundle\Dispatcher;

use Ulabox\Bundle\GearmanBundle\Manager\GearmanManager;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Symfony\Component\EventDispatcher\Event;

/**
 * Async event.
 *
 * @author Ivannis Suárez Jérez <ivannis.suarez@gmail.com>
 */
class EventAsync implements EventAsyncInterface
{
    const GEARMAN_CLIENT_CLASS_ALIAS = 'UlaboxGearmanBundle:GearmanClient';
    const GEARMAN_WORKER_CLASS_ALIAS = 'UlaboxGearmanBundle:EventWorker';

    /**
     * @var string
     */
    protected $useVia;

    /**
     * The gearman manager
     *
     * @var GearmanManager
     */
    protected $manager;

    /**
     * Constructor
     *
     * @param GearmanManager $manager The gearman manager
     */
    public function __construct(GearmanManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param $useVia
     */
    public function setUseVia($useVia)
    {
        $this->useVia = $useVia;
    }

    /**
     * @return mixed
     */
    public function getUseVia()
    {
        return $this->useVia;
    }

    /**
     * @return mixed
     */
    private function getGearmanClient()
    {
        return $this->manager->getClient(self::GEARMAN_CLIENT_CLASS_ALIAS);
    }

    /**
     * @return mixed
     */
    private function getGearmanWorker()
    {
        return $this->manager->getWorker(self::GEARMAN_WORKER_CLASS_ALIAS);
    }

    /**
     * {@inheritdoc}
     */
    public function forward(Event $event)
    {
        if (!($event instanceof AsyncEventInterface)) {
            throw new \Exception("The class ".get_class($event). " should be implement the Ulabox\Bundle\GearmanBundle\Dispatcher\AsyncEventInterface");
        }

        // get the generic gearman client
        $client = $this->getGearmanClient();

        // find the event worker
        $worker = $this->getGearmanWorker();

        // now you should tell the client that worker must be run
        $client->setWorker($worker);

        // serialize the event and sent to a queue
        $serializeEvent = serialize(array(get_class($event), $event->getName(), $event->getArguments()));

        // do the job in backgroud
        $client->doBackgroundJob('dispatch', $serializeEvent);
    }
}