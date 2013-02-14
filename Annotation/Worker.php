<?php

namespace Ulabox\Bundle\GearmanBundle\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 */
class Worker
{
    /**
     * Servers name
     *
     * @var array
     */
    protected $servers = array('127.0.0.1');

    /**
     * Worker iterations
     *
     * @var integer
     */
    protected $iterations = 100;

    /**
     * Constructor
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        if (isset($data['servers'])) {
            $this->servers = $data['servers'];
        }

        if (isset($data['iterations'])) {
            $this->iterations = (int) $data['iterations'];
        }
    }

    /**
     * Get server name
     *
     * @return array
     */
    public function getServers()
    {
        return $this->servers;
    }

    /**
     * Get worker iterations
     *
     * @return integer
     */
    public function getIterations()
    {
        return $this->iterations;
    }
}
