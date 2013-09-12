<?php

namespace Ulabox\Bundle\GearmanBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Async event controller.
 *
 * @author Ivannis Suárez Jérez <ivannis.suarez@gmail.com>
 */
class EventAsyncController extends Controller
{
    /**
     * Reconstruct an async event and dispatch
     *
     * @param Request $request The request
     *
     * @return JsonResponse
     */
    public function handleAction(Request $request)
    {
        try {
            $event = $this->getEventFactory()->getReconstructedEvent($request);

            $this->getEventDispatcher()->dispatch($event->getName(), $event);

            return new JsonResponse(array(
                'status'  => 'OK',
                'message' => $event->getName().' dispatched'
            ));
        } catch (\Exception $e) {
            return new JsonResponse(array(
                'status'  => 'FAILED',
                'message' => $e->getMessage()
            ));
        }
    }

    /**
     * Get the async event factory
     *
     * @return EventAsyncFactoryInterface
     */
    private function getEventFactory()
    {
        return $this->container->get('ulabox_gearman.event_async_factory');
    }

    /**
     * Get event dispatcher
     *
     * @return EventDispatcherInterface
     */
    private function getEventDispatcher()
    {
        return $this->container->get('event_dispatcher');
    }
}