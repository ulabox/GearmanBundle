<?php

namespace Ulabox\Bundle\GearmanBundle\Annotation;

/**
 * Worker annotation class.
 *
 * @Annotation
 * @Target("CLASS")
 *
 * @author Ivannis Suárez Jérez <ivannis.suarez@gmail.com>
 */
class Worker
{
    /**
     * Servers name
     *
     * @var array
     */
    protected $servers = array();

    /**
     * Worker iterations
     *
     * @var integer
     */
    protected $iterations = null;

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
