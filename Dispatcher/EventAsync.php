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
     * {@inheritdoc}
     */
    public function forward(Event $event)
    {
        if (!($event instanceof AsyncEventInterface)) {
            throw new \Exception("The class ".get_class($event). " should be implement the Ulabox\Bundle\GearmanBundle\Dispatcher\AsyncEventInterface");
        }

        // get the generic gearman client
        $client = $this->manager->getClient('UlaboxGearmanBundle:GearmanClient');

        // find the event worker
        $worker = $this->manager->getWorker('UlaboxGearmanBundle:EventWorker');

        // now you should tell the client that worker must be run
        $client->setWorker($worker);

        // serialize the event and sent to a queue
        $serializeEvent = serialize(array(get_class($event), $event->getName(), $event->getArguments()));

        // do the job in backgroud
        $client->doBackgroundJob('dispatch', $serializeEvent);
    }
}