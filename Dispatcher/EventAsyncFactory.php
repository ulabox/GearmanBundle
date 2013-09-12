<?php

namespace Ulabox\Bundle\GearmanBundle\Dispatcher;

use Symfony\Component\HttpFoundation\Request;

/**
 * Async event factory.
 *
 * @author Ivannis Suárez Jérez <ivannis.suarez@gmail.com>
 */
class EventAsyncFactory implements EventAsyncFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getReconstructedEvent(Request $request)
    {
        // deserialize and reconstructs the original event
        list($eventClass, $eventName, $eventArguments) = unserialize($request->getContent());

        $class = new \ReflectionClass($eventClass);
        if (!in_array('Ulabox\\Bundle\\GearmanBundle\\Dispatcher\\AsyncEventInterface', $class->getInterfaceNames())) {
            throw new \Exception("The class ".$eventClass. " should be implement the Ulabox\\Bundle\\GearmanBundle\\Dispatcher\\AsyncEventInterface");
        }

        $event = $class->newInstanceWithoutConstructor();
        $event->setName($eventName);
        $event->setArguments($eventArguments);

        return $event;
    }
}