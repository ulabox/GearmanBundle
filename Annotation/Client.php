<?php

namespace Ulabox\Bundle\GearmanBundle\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 */
class Client
{
    /**
     * Worker name
     *
     * @var string
     */
    protected $worker;

    /**
     * Servers name
     *
     * @var array
     */
    protected $servers = array();

    /**
     * Tasks name
     *
     * @var array
     */
    protected $tasks = array();

    /**
     * Constructor
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        if (isset($data['worker']))
            $this->worker = $data['worker'];

        if (isset($data['servers']))
            $this->servers = $data['servers'];

        if (isset($data['tasks']))
            $this->tasks = $data['tasks'];
    }

    /**
     * Get worker name
     *
     * @return string
     */
    public function getWorkerName()
    {
        return $this->worker;
    }

    /**
     * Get servers name
     *
     * @return array
     */
    public function getServers()
    {
        return $this->servers;
    }

    /**
     * Get tasks name
     *
     * @return array
     */
    public function getTasks()
    {
        return $this->tasks;
    }
}