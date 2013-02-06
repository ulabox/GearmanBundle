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
     * Constructor
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        if (isset($data['servers']))
            $this->servers = $data['servers'];
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
}
