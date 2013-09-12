<?php

namespace Ulabox\Bundle\GearmanBundle\Dispatcher;

/**
 * Interface for async event.
 *
 * @author Ivannis Suárez Jérez <ivannis.suarez@gmail.com>
 */
interface AsyncEventInterface
{
    /**
     * Getter for all event arguments.
     *
     * @return array
     */
    public function getArguments();

    /**
     * Set event arguments.
     *
     * @param array $args Arguments.
     *
     * @return GenericEvent
     */
    public function setArguments(array $args = array());
}