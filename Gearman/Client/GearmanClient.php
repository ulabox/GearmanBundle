<?php

namespace Ulabox\Bundle\GearmanBundle\Gearman\Client;

use Ulabox\Bundle\GearmanBundle\Annotation\Client;
use Ulabox\Bundle\GearmanBundle\Model\Client as BaseClient;

/**
 * Base gearman client.
 *
 * @Client()
 *
 * @author Ivannis Suárez Jérez <ivannis.suarez@gmail.com>
 */
class GearmanClient extends BaseClient
{
    /**
     * The taks result
     *
     * @var array
     */
    protected $result = array();

    /**
     * The complete task callback.
     *
     * @param GearmanTask $task
     */
    public function complete($task)
    {
        $this->result[$task->unique()] = json_decode($task->data(), true);
    }

    /**
     * Get the callback results.
     *
     * @return array
     */
    public function getResults()
    {
        return $this->result;
    }

    /**
     * Get the callback result by a key.
     *
     * @param mixed $key
     *
     * @return mixed
     */
    public function getResult($key)
    {
        return isset($this->result[$key]) ? $this->result[$key] : null;
    }
}