<?php

namespace Ulabox\Bundle\GearmanBundle\Annotation;


/**
 * @Annotation
 * @Target("METHOD")
 */
class Job
{
    /**
     * The job name
     *
     * @var string
     */
    protected $name = null;

    /**
     * Constructor
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        if (isset($data['name']))
            $this->name = $data['name'];
    }

    /**
     * Get job name
     */
    public function getName()
    {
        return $this->name;
    }
}
