<?php

namespace Ulabox\Bundle\GearmanBundle\Metadata;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Metadata\ClassMetadata as BaseClassMetadata;
use Metadata\MethodMetadata;

/**
 * Metadata class.
 *
 * @author Ivannis Suárez Jérez <ivannis.suarez@gmail.com>
 */
class ClassMetadata extends BaseClassMetadata
{
    /**
     * True if class is a worker, false otherwise
     *
     * @var boolean
     */
    protected $isWorker = false;

    /**
     * True if class is a client, false otherwise
     *
     * @var boolean
     */
    protected $isClient = false;

    /**
     * The client/task worker name
     *
     * @var string
     */
    protected $workerName;

    /**
     * Worker servers name
     *
     * @var string
     */
    protected $servers;

    /**
     * Worker iterations
     *
     * @var integer
     */
    protected $workerIterations = 100;

    /**
     * Client tasks name
     *
     * @var string
     */
    protected $tasks;

    /**
     * The worker bundle name
     *
     * @var string
     */
    protected $bundleName;

    /**
     * The jobs methods metadata
     * @var ArrayCollection
     */
    protected $jobs;

    /**
     * The methods metadata
     * @var ArrayCollection
     */
    protected $methods;

    /**
     * Constructor
     * @param string $name
     */
    public function __construct($name)
    {
        $this->jobs = new ArrayCollection();
        $this->methods = new ArrayCollection();

        parent::__construct($name);
    }

    /**
     * Get servers name
     *
     * @return string
     */
    public function getServers()
    {
        return $this->servers;
    }

    /**
     * Set servers name
     *
     * @param string $servers
     */
    public function setServers($servers)
    {
        $this->servers = $servers;
    }

    /**
     * Get tasks name
     *
     * @return string
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * Set tasks name
     *
     * @param string $tasks
     */
    public function setTasks($tasks)
    {
        $this->tasks = $tasks;
    }

    /**
     * Determine if class is a worker
     *
     * @return boolean
     */
    public function isWorker()
    {
        return $this->isWorker;
    }

    /**
     * Set worker value
     *
     * @param boolean $worker
     */
    public function setWorker($worker)
    {
        $this->isWorker = $worker;
    }

    /**
     * Determine if class is a client
     *
     * @return boolean
     */
    public function isClient()
    {
        return $this->isClient;
    }

    /**
     * Set client value
     *
     * @param boolean $client
     */
    public function setClient($client)
    {
        $this->isClient = $client;
    }

    /**
     * Return the worker name of this client/task
     *
     * @return string
     */
    public function getWorkerName()
    {
        return $this->workerName;
    }

    /**
     * Set the worker name of this client/task
     *
     * @param string $name
     */
    public function setWorkerName($name)
    {
        $this->workerName = $name;
    }

    /**
     * Get bundle name
     *
     * @return string
     */
    public function getBundleName()
    {
        return $this->bundleName;
    }

    /**
     * Set bundle name
     *
     * @param string $bundleName
     */
    public function setBundleName($bundleName)
    {
        $this->bundleName = $bundleName;
    }

    /**
     * Get worker slug
     *
     * @return string
     */
    public function getWorkerSlug()
    {
        $className = substr($this->name, strrpos($this->name, '\\') + 1);

        return $this->bundleName.':'.$className;
    }

    /**
     * Return the worker iterations
     *
     * @return integer
     */
    public function getWorkerIterations()
    {
        return $this->workerIterations;
    }

    /**
     * Set the worker iterations
     *
     * @param integer $iterations
     */
    public function setWorkerIterations($iterations)
    {
        $this->workerIterations = $iterations;
    }

    /**
     * This should return true only when worker has jobs.
     *
     * @return Boolean
     */
    public function hasJobs()
    {
        return !$this->jobs->isEmpty();
    }

    /**
     * Get job list for this worker
     *
     * @return ArrayCollection
     */
    public function getJobs()
    {
        return $this->jobs;
    }

    /**
     * Sets all worker jobs.
     *
     * @param Collection $jobs
     */
    public function setJobs(Collection $jobs)
    {
        $this->jobs = $jobs;
    }

    /**
     * Adds job.
     *
     * @param MethodMetadata $job
     */
    public function addJob(MethodMetadata $job)
    {
        if (!$this->hasJob($job)) {
            $this->jobs->add($job);
        }
    }

    /**
     * Removes job from worker.
     *
     * @param MethodMetadata $job
     */
    public function removeJob(MethodMetadata $job)
    {
        if ($this->hasJob($job)) {
            $this->jobs->removeElement($job);
        }
    }

    /**
     * Checks whether worker has given job.
     *
     * @param MethodMetadata $job
     *
     * @return Boolean
     */
    public function hasJob(MethodMetadata $job)
    {
        return $this->jobs->contains($job);
    }

    /**
     * This should return true only when class has methods.
     *
     * @return Boolean
     */
    public function hasMethods()
    {
        return !$this->methods->isEmpty();
    }

    /**
     * Get methods list for this class
     *
     * @return ArrayCollection
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * Sets all class methods.
     *
     * @param Collection $methods
     */
    public function setMethods(Collection $methods)
    {
        $this->methods = $methods;
    }

    /**
     * Adds method.
     *
     * @param MethodMetadata $method
     */
    public function addMethod(MethodMetadata $method)
    {
        if (!$this->hasMethod($method)) {
            $this->methods->add($method);
        }
    }

    /**
     * Removes method from class.
     *
     * @param MethodMetadata $method
     */
    public function removeMethod(MethodMetadata $method)
    {
        if ($this->hasMethod($method)) {
            $this->methods->removeElement($method);
        }
    }

    /**
     * Checks whether class has given method.
     *
     * @param string $method
     *
     * @return Boolean
     */
    public function hasMethod($method)
    {
        foreach ($this->methods as $methodMetadata) {
            if ($methodMetadata->name == $method) {
                return true;
            }
        }

        return false;
    }
}
