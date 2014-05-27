<?php

namespace Ulabox\Bundle\GearmanBundle\Model;

use Ulabox\Bundle\GearmanBundle\Manager\GearmanManager;
use Metadata\MetadataFactory;

/**
 * Default gearman client.
 *
 * @author Ivannis Suárez Jérez <ivannis.suarez@gmail.com>
 */
class Client implements ClientInterface
{
    /**
     * Client worker
     *
     * @var string
     */
    private $worker;

    /**
     * Client servers name
     *
     * @var array
     */
    private $servers;

    /**
     * The gearman client instance
     *
     * @var \GearmanClient
     */
    private $gearmanClient = null;

    /**
     * The metadata information
     *
     * @var ClassMetadata
     */
    protected $metadata;

    /**
     * The metadata factory
     *
     * @var MetadataFactory
     */
    protected $metadataFactory;

    /**
     * The gearman manager
     *
     * @var GearmanManager
     */
    protected $manager;

    /**
     * Constructor
     *
     * @param MetadataFactory $metadataFactory The metadata factory
     * @param array           $servers         The list of servers
     */
    public final function __construct(MetadataFactory $metadataFactory, $servers)
    {
        $this->metadata        = $metadataFactory->getMetadataForClass(get_class($this))->getOutsideClassMetadata();
        $this->metadataFactory = $metadataFactory;

        $annotationServers = $this->metadata->getServers();
        if (count($annotationServers) > 0) {
            $this->servers = $annotationServers;
        } else {
            $this->servers = $servers;
        }

        $this->gearmanClient = new \GearmanClient();
        $this->addServers();
    }

    /**
     * {@inheritdoc}
     */
    public function setManager(GearmanManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function setWorker(WorkerInterface $worker)
    {
        $this->worker = $worker;
    }

    /**
     * {@inheritdoc}
     */
    public function configure()
    {

    }

    /**
     * Get gearman client instance
     *
     * @return GearmanClient
     */
    private function getGearmanClient()
    {
        return $this->gearmanClient;
    }

    /**
     * Add servers to gearman client
     */
    private function addServers()
    {
        foreach ($this->servers as $server) {
            $config = explode(":", $server);
            $host = $config[0];
            $port = isset($config[1]) ? $config[1] : 4730;

            $this->gearmanClient->addServer($host, $port);
        }
    }

    /**
     * Get the method name
     *
     * @param  string $jobName
     *
     * @return string
     */
    private function getMethodName($jobName)
    {
        return substr($jobName, strrpos($jobName, ':') + 1);
    }

    /**
     * Get the worker namespace
     *
     * @param  string $jobName
     *
     * @return string
     */
    private function getWorkerNamespace($jobName)
    {
        return substr($jobName, 0, strrpos($jobName, ':'));
    }

    /**
     * Resolve job name
     *
     * @param  string $jobName
     *
     * @return string
     */
    private function resolveJobName($jobName)
    {
        if (strpos($jobName, ':')) {
            return $jobName;
        }

        $bundleName = $this->metadata->getBundleName();
        $workerName = $this->metadata->getWorkerName();

        if ($this->worker !== null) {
            $metadata   = $this->metadataFactory->getMetadataForClass(get_class($this->worker))->getOutsideClassMetadata();
            $className  = get_class($this->worker);
            $workerName = substr($className, strrpos($className, '\\') + 1);
            $bundleName = $metadata->getBundleName();
        }

        return sprintf('%s:%s:%s', $bundleName, $workerName, $jobName);
    }

    /**
     * Validate the parameters
     *
     * @param string $jobName
     * @param $params
     */
    private function validate($jobName, $params, $method)
    {
        if (!is_string($params))
            throw new \InvalidArgumentException('Argument 2 passed to '.get_class($this).'::'.$method.'() must be an instance of string');

        $jobName = $this->resolveJobName($jobName);
        $workerNamespace = $this->getWorkerNamespace($jobName);

        if (!$this->manager->hasWorker($workerNamespace)) {
            if ($this->worker == null) {
                throw new \RuntimeException('There is not worker registered with name '.$workerNamespace);
            }
        }
    }

    /**
     * Run a task in the background
     *
     * @param string $jobName
     * @param $params
     * @param string $unique
     */
    public function doBackgroundJob($jobName, $params = '', $unique = null)
    {
        $this->validate($jobName, $params, 'doBackgroundJob');
        $jobName = $this->resolveJobName($jobName);

        return $this->getGearmanClient()->doBackground($jobName, $params, $unique);
    }

    /**
     * Run a single high priority task
     *
     * @param string $jobName
     * @param $params
     * @param string $unique
     */
    public function doHighJob($jobName, $params = '', $unique = null)
    {
        $this->validate($jobName, $params, 'doHighJob');
        $jobName = $this->resolveJobName($jobName);

        return $this->getGearmanClient()->doHigh($jobName, $params, $unique);
    }

    /**
     * Run a high priority task in the background
     *
     * @param string $jobName
     * @param $params
     * @param string $unique
     */
    public function doHighBackgroundJob($jobName, $params = '', $unique = null)
    {
        $this->validate($jobName, $params, 'doHighBackgroundJob');
        $jobName = $this->resolveJobName($jobName);

        return $this->getGearmanClient()->doHighBackground($jobName, $params, $unique);
    }


    /**
     * Run a single low priority task
     *
     * @param string $jobName
     * @param $params
     * @param string $unique
     */
    public function doLowJob($jobName, $params = '', $unique = null)
    {
        $this->validate($jobName, $params, 'doLowJob');
        $jobName = $this->resolveJobName($jobName);

        return $this->getGearmanClient()->doLow($jobName, $params, $unique);
    }

    /**
     * Run a low priority task in the background
     *
     * @param string $jobName
     * @param $params
     * @param string $unique
     */
    public function doLowBackgroundJob($jobName, $params = '', $unique = null)
    {
        $this->validate($jobName, $params, 'doLowBackgroundJob');
        $jobName = $this->resolveJobName($jobName);

        return $this->getGearmanClient()->doLowBackground($jobName, $params, $unique);
    }

    /**
     * Run a single task and return a result
     *
     * @param string $jobName
     * @param $params
     * @param string $unique
     */
    public function doNormalJob($jobName, $params = '', $unique = null)
    {
        $this->validate($jobName, $params, 'doNormalJob');

        $jobName = $this->resolveJobName($jobName);
        $methodName = $this->getMethodName($jobName);

        do {
            $result = $this->getGearmanClient()->do($jobName, $params, $unique);

            switch($this->getGearmanClient()->returnCode()) {
                case GEARMAN_WORK_DATA:
                    $callbackMethod = sprintf('%s_%s', $methodName, 'data');
                    if ($this->metadata->hasMethod($callbackMethod)) {
                        $this->{$callbackMethod}($result);
                    }
                    break;
                case GEARMAN_WORK_STATUS:
                    list($numerator, $denominator) = $this->getGearmanClient()->doStatus();
                    $callbackMethod = sprintf('%s_%s', $methodName, 'status');
                    if ($this->metadata->hasMethod($callbackMethod)) {
                        $this->{$callbackMethod}($numerator/$denominator);
                    }
                    break;
                case GEARMAN_WORK_FAIL:
                    $callbackMethod = sprintf('%s_%s', $methodName, 'fail');
                    if ($this->metadata->hasMethod($callbackMethod)) {
                        $this->{$callbackMethod}();
                    }
                    exit;
                case GEARMAN_SUCCESS:
                    $callbackMethod = sprintf('%s_%s', $methodName, 'success');
                    if ($this->metadata->hasMethod($callbackMethod)) {
                        $this->{$callbackMethod}($result);
                    }
                    break;
                default:
                    exit;
            }

        } while($this->getGearmanClient()->returnCode() != GEARMAN_SUCCESS);
    }

    /**
     * Register task callback functions
     *
     * @param string $jobName
     */
    public function registerCallbacks($jobName)
    {
        $methodName = $this->getMethodName($jobName);

        // register callback functions if exist
        $callbackMethod = sprintf('%s_%s', $methodName, 'created');
        if ($this->metadata->hasMethod($callbackMethod)) {
            $this->getGearmanClient()->setCreatedCallback(array($this, $callbackMethod));
        }

        $callbackMethod = sprintf('%s_%s', $methodName, 'data');
        if ($this->metadata->hasMethod($callbackMethod)) {
            $this->getGearmanClient()->setDataCallback(array($this, $callbackMethod));
        }

        $callbackMethod = sprintf('%s_%s', $methodName, 'status');
        if ($this->metadata->hasMethod($callbackMethod)) {
            $this->getGearmanClient()->setStatusCallback(array($this, $callbackMethod));
        }

        $callbackMethod = sprintf('%s_%s', $methodName, 'complete');
        if ($this->metadata->hasMethod($callbackMethod)) {
            $this->getGearmanClient()->setCompleteCallback(array($this, $callbackMethod));
        }

        $callbackMethod = sprintf('%s_%s', $methodName, 'fail');
        if ($this->metadata->hasMethod($callbackMethod)) {
            $this->getGearmanClient()->setFailCallback(array($this, $callbackMethod));
        }

        return $this;
    }

    /**
     * Add a task to be run in parallel
     *
     * @param string $jobName
     * @param $params
     * @param string $unique
     */
    public function addTask($jobName, $params = '', $unique = null)
    {
        $this->validate($jobName, $params, 'addTask');

        // register callback functions
        $jobName = $this->resolveJobName($jobName);
        $this->registerCallbacks($jobName);

        $this->getGearmanClient()->addTask($jobName, $params, $unique);

        return $this;
    }

    /**
     * Add a background task to be run in parallel
     *
     * @param string $jobName
     * @param $params
     * @param string $unique
     */
    public function addTaskBackground($jobName, $params = '', $unique = null)
    {
        $this->validate($jobName, $params, 'addTaskBackground');

        // register callback functions
        $jobName = $this->resolveJobName($jobName);
        $this->registerCallbacks($jobName);

        $this->getGearmanClient()->addTaskBackground($jobName, $params, $unique);

        return $this;
    }

    /**
     * Add a high priority task to run in parallel
     *
     * @param string $jobName
     * @param $params
     * @param string $unique
     */
    public function addTaskHigh($jobName, $params = '', $unique = null)
    {
        $this->validate($jobName, $params, 'addTaskHigh');

        // register callback functions
        $jobName = $this->resolveJobName($jobName);
        $this->registerCallbacks($jobName);

        $this->getGearmanClient()->addTaskHigh($jobName, $params, $unique);

        return $this;
    }

    /**
     *  Add a high priority background task to be run in parallel
     *
     * @param string $jobName
     * @param $params
     * @param string $unique
     */
    public function addTaskHighBackground($jobName, $params = '', $unique = null)
    {
        $this->validate($jobName, $params, 'addTaskHighBackground');

        // register callback functions
        $jobName = $this->resolveJobName($jobName);
        $this->registerCallbacks($jobName);

        $this->getGearmanClient()->addTaskHighBackground($jobName, $params, $unique);

        return $this;
    }

    /**
     * Add a low priority task to run in parallel
     *
     * @param string $jobName
     * @param $params
     * @param string $unique
     */
    public function addTaskLow($jobName, $params = '', $unique = null)
    {
        $this->validate($jobName, $params, 'addTaskLow');

        // register callback functions
        $jobName = $this->resolveJobName($jobName);
        $this->registerCallbacks($jobName);

        $this->getGearmanClient()->addTaskLow($jobName, $params, $unique);

        return $this;
    }

    /**
     * Add a low priority background task to be run in parallel
     *
     * @param string $jobName
     * @param $params
     * @param string $unique
     */
    public function addTaskLowBackground($jobName, $params = '', $unique = null)
    {
        $this->validate($jobName, $params, 'addTaskLowBackground');

        // register callback functions
        $jobName = $this->resolveJobName($jobName);
        $this->registerCallbacks($jobName);

        $this->getGearmanClient()->addTaskLowBackground($jobName, $params, $unique);

        return $this;
    }

    /**
     * Run a tasks and return a result
     */
    public function runTasks()
    {
        if (!$this->getGearmanClient()->runTasks()) {
            exit;
        }
    }
}
