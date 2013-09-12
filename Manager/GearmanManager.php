<?php

namespace Ulabox\Bundle\GearmanBundle\Manager;

use Ulabox\Bundle\GearmanBundle\Model\ClientInterface;
use Ulabox\Bundle\GearmanBundle\Model\WorkerInterface;
use Ulabox\Bundle\GearmanBundle\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Manager class for manipulate workers and clients.
 *
 * @author Ivannis Suárez Jérez <ivannis.suarez@gmail.com>
 */
class GearmanManager
{
    /**
     * Array of worker.
     *
     * @var array
     */
    protected $workers = array();

    /**
     * Array of ordered worker.
     *
     * @var array
     */
    protected $orderedWorkers = array();

    /**
     * Determines if we must order workers by number
     *
     * @var boolean
     */
    protected $orderWorkersByNumber = false;

    /**
     * Determines if we must order workers by its dependencies
     *
     * @var boolean
     */
    protected $orderWorkersByDependencies = false;

    /**
     * Array of client.
     *
     * @var array
     */
    protected $clients = array();

    /**
     * The kernel instance
     *
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * Constructor.
     *
     * @param KernelInterface $kernel       The kernel instance
     * @param LoaderInterface $workerLoader The worker loader
     * @param LoaderInterface $clientLoader The client loader
     */
    public function __construct(KernelInterface $kernel, LoaderInterface $workerLoader, LoaderInterface $clientLoader)
    {
        $this->kernel = $kernel;
        foreach ($kernel->getBundles() as $bundle) {
            $path = $bundle->getPath() . '/' . $kernel->getContainer()->getParameter('ulabox_gearman.worker_dir');
            if (is_dir($path)) {
                // load workers
                foreach ($workerLoader->loadFromDirectory($path, $bundle->getName()) as $worker) {
                    $this->addWorker($worker);
                }
            }

            $path = $bundle->getPath() . '/' . $kernel->getContainer()->getParameter('ulabox_gearman.client_dir');
            if (is_dir($path)) {
                // load clients
                foreach ($clientLoader->loadFromDirectory($path, $bundle->getName()) as $client) {
                    $this->addClient($client);
                }
            }
        }

        $this->configure();
    }

    /**
     * Configure workers and clients
     */
    private function configure()
    {
        foreach ($this->workers as $worker) {
            // configure worker
            if ($worker instanceof \Ulabox\Bundle\GearmanBundle\Model\ContainerAwareWorker) {
                $worker->setContainer($this->kernel->getContainer());
            }

            $worker->setManager($this);
            $worker->configure();
        }

        foreach ($this->clients as $client) {
            // configure client
            $client->setManager($this);
            $client->configure();
        }
    }

    /**
     * Get worker
     *
     * @param string $namespace The worker namespace
     *
     * @return WorkerInterface
     */
    public function getWorker($namespace)
    {
        $scope = str_replace('/', '\\', $this->kernel->getContainer()->getParameter('ulabox_gearman.worker_dir'));
        $className = $this->getClassName($namespace, $scope);
        foreach ($this->workers as $worker) {
            if (get_class($worker) == $className) {
                return $worker;
            }
        }

        return null;
    }

    /**
     * Has worker?
     *
     * @param string $namespace The worker namespace
     *
     * @return boolean
     */
    public function hasWorker($namespace)
    {
        $scope = str_replace('/', '\\', $this->kernel->getContainer()->getParameter('ulabox_gearman.worker_dir'));
        $className = $this->getClassName($namespace, $scope);

        return isset($this->workers[$className]);
    }

    /**
     * Add a worker object instance to the loader.
     *
     * @param WorkerInterface $worker The worker instance
     */
    public function addWorker(WorkerInterface $worker)
    {
        $workerClass = get_class($worker);

        if (!isset($this->workers[$workerClass])) {
            if ($worker instanceof OrderedWorkerInterface && $worker instanceof DependentWorkerInterface) {
                throw new \InvalidArgumentException(sprintf(
                    'Class "%s" can\'t implement "%s" and "%s" at the same time.',
                    get_class($worker),
                    'OrderedWorkerInterface',
                    'DependentWorkerInterface'
                ));
            } elseif ($worker instanceof OrderedWorkerInterface) {
                $this->orderWorkersByNumber = true;
            } elseif ($worker instanceof DependentWorkerInterface) {
                $this->orderWorkersByDependencies = true;
            }

            $this->workers[$workerClass] = $worker;
        }
    }

    /**
     * Returns the array of data workers to execute.
     *
     * @return array $workers
     */
    public function getWorkers()
    {
        $this->orderedWorkers = array();

        if ($this->orderWorkersByNumber) {
            $this->orderWorkersByNumber();
        }

        if ($this->orderWorkersByDependencies) {
            $this->orderWorkersByDependencies();
        }

        if (!$this->orderWorkersByNumber && !$this->orderWorkersByDependencies) {
            $this->orderedWorkers = $this->workers;
        }

        return $this->orderedWorkers;
    }

    /**
     * Orders workers by number
     *
     * @todo maybe there is a better way to handle reordering
     * @return void
     */
    private function orderWorkersByNumber()
    {
        $this->orderedWorkers = $this->workers;
        usort($this->orderedWorkers, function($a, $b) {
            if ($a instanceof OrderedWorkerInterface && $b instanceof OrderedWorkerInterface) {
                if ($a->getOrder() === $b->getOrder()) {
                    return 0;
                }

                return $a->getOrder() < $b->getOrder() ? -1 : 1;
            } elseif ($a instanceof OrderedWorkerInterface) {
                return $a->getOrder() === 0 ? 0 : 1;
            } elseif ($b instanceof OrderedWorkerInterface) {
                return $b->getOrder() === 0 ? 0 : -1;
            }

            return 0;
        });
    }


    /**
     * Orders workers by dependencies
     *
     * @return void
     */
    private function orderWorkersByDependencies()
    {
        $sequenceForClasses = array();

        // If workers were already ordered by number then we need
        // to remove classes which are not instances of OrderedWorkerInterface
        // in case workers implementing DependentWorkerInterface exist.
        // This is because, in that case, the method orderWorkersByDependencies
        // will handle all workers which are not instances of
        // OrderedWorkerInterface
        if ($this->orderWorkersByNumber) {
            $count = count($this->orderedWorkers);

            for ($i = 0; $i < $count; ++$i) {
                if (!($this->orderedWorkers[$i] instanceof OrderedWorkerInterface)) {
                    unset($this->orderedWorkers[$i]);
                }
            }
        }

        // First we determine which classes has dependencies and which don't
        foreach ($this->workers as $worker) {
            $workerClass = get_class($worker);

            if ($worker instanceof OrderedWorkerInterface) {
                continue;
            } elseif ($worker instanceof DependentWorkerInterface) {
                $dependenciesClasses = $worker->getDependencies();

                $this->validateDependencies($dependenciesClasses);

                if (!is_array($dependenciesClasses) || empty($dependenciesClasses)) {
                    throw new \InvalidArgumentException(sprintf('Method "%s" in class "%s" must return an array of classes which are dependencies for the worker, and it must be NOT empty.', 'getDependencies', $workerClass));
                }

                if (in_array($workerClass, $dependenciesClasses)) {
                    throw new \InvalidArgumentException(sprintf('Class "%s" can\'t have itself as a dependency', $workerClass));
                }

                // We mark this class as unsequenced
                $sequenceForClasses[$workerClass] = -1;
            } else {
                // This class has no dependencies, so we assign 0
                $sequenceForClasses[$workerClass] = 0;
            }
        }

        // Now we order workers by sequence
        $sequence = 1;
        $lastCount = -1;

        while (($count = count($unsequencedClasses = $this->getUnsequencedClasses($sequenceForClasses))) > 0 && $count !== $lastCount) {
            foreach ($unsequencedClasses as $key => $class) {
                $worker = $this->workers[$class];
                $dependencies = $worker->getDependencies();
                $unsequencedDependencies = $this->getUnsequencedClasses($sequenceForClasses, $dependencies);

                if (count($unsequencedDependencies) === 0) {
                    $sequenceForClasses[$class] = $sequence++;
                }
            }

            $lastCount = $count;
        }

        $orderedWorkers = array();

        // If there're workers unsequenced left and they couldn't be sequenced,
        // it means we have a circular reference
        if ($count > 0) {
            $msg = 'Classes "%s" have produced a CircularReferenceException. ';
            $msg .= 'An example of this problem would be the following: Class C has class B as its dependency. ';
            $msg .= 'Then, class B has class A has its dependency. Finally, class A has class C as its dependency. ';
            $msg .= 'This case would produce a CircularReferenceException.';

            throw new CircularReferenceException(sprintf($msg, implode(',', $unsequencedClasses)));
        } else {
            // We order the classes by sequence
            asort($sequenceForClasses);

            foreach ($sequenceForClasses as $class => $sequence) {
                // If workers were ordered
                $orderedWorkers[] = $this->workers[$class];
            }
        }

        $this->orderedWorkers = array_merge($this->orderedWorkers, $orderedWorkers);
    }

    /**
     * Validate class dependencies.
     *
     * @param array $dependenciesClasses Array of dependencies classes
     *
     * @return boolean
     */
    private function validateDependencies($dependenciesClasses)
    {
        $loadedWorkerClasses = array_keys($this->workers);

        foreach ($dependenciesClasses as $class) {
            if (!in_array($class, $loadedWorkerClasses)) {
                throw new \RuntimeException(sprintf('Worker "%s" was declared as a dependency, but it should be added in worker loader first.', $class));
            }
        }

        return true;
    }

    /**
     * Get client
     *
     * @param string $namespace The client namespace
     *
     * @return ClientInterface
     */
    public function getClient($namespace)
    {
        $scope = str_replace('/', '\\', $this->kernel->getContainer()->getParameter('ulabox_gearman.client_dir'));
        $className = $this->getClassName($namespace, $scope);

        foreach ($this->clients as $client) {
            if (get_class($client) == $className) {
                return $client;
            }
        }

        return null;
    }

    /**
     * Has client?
     *
     * @param string $className The class name
     *
     * @return boolean
     */
    public function hasClient($className)
    {
        return isset($this->clients[$className]);
    }

    /**
     * Add a client object instance to the loader.
     *
     * @param ClientInterface $client The client instance
     */
    public function addClient(ClientInterface $client)
    {
        $clientClass = get_class($client);

        if (!$this->hasClient($clientClass)) {
            $this->clients[$clientClass] = $client;
        }
    }

    /**
     * Returns the array of data clients to execute.
     *
     * @return array $clients
     */
    public function getClients()
    {
        return $this->clients;
    }

    /**
     * Get the real class name for a given namespace and scope
     *
     * @param string $namespace The class namespace
     * @param string $scope     The scope
     *
     * @return string
     */
    private function getClassName($namespace, $scope)
    {
        $bundleName = substr($namespace, 0, strpos($namespace, ':'));
        $workerName = substr($namespace, strrpos($namespace, ':') + 1);

        return sprintf("%s\%s\%s", $this->kernel->getBundle($bundleName)->getNamespace(), $scope, $workerName);
    }
}