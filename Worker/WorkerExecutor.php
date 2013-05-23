<?php

namespace Ulabox\Bundle\GearmanBundle\Worker;

use Symfony\Component\Console\Output\OutputInterface;
use Ulabox\Bundle\GearmanBundle\Model\WorkerInterface;
use Ulabox\Bundle\GearmanBundle\Metadata\ClassMetadata;
use Metadata\MetadataFactory;

/**
 * Worker executor
 *
 * @author Ivannis Suárez Jérez <ivan@ulabox.com>
 */
class WorkerExecutor
{
    /**
     * The gearman worker instance
     *
     * @var \GearmanWorker
     */
    protected $gearmanWorker;

    /**
     * Worker servers name
     *
     * @var array
     */
    private $servers;

    /**
     * Workers count
     *
     * @var integer
     */
    protected $workersCount;

    /**
     * The metadata factory class
     *
     * @var MetadataFactory
     */
    protected $metadataFactory;

    /**
     * Worker iterations
     *
     * @var integer
     */
    protected $iterations = 100;

    /**
     * Constructor
     *
     * @param MetadataFactory $metadataFactory
     */
    public function __construct(MetadataFactory $metadataFactory, $servers, $iterations)
    {
        $this->metadataFactory = $metadataFactory;
        $this->gearmanWorker   = new \GearmanWorker();
        $this->workersCount    = 0;
        $this->servers         = $servers;
        $this->iterations      = $iterations;
    }

    /**
     * Add worker to gearman
     *
     * @param WorkerInterface $worker
     */
    public function addWorker(WorkerInterface $worker)
    {
        $metadata   = $this->metadataFactory->getMetadataForClass(get_class($worker))->getOutsideClassMetadata();
        $servers    = $this->servers;

        $annotationServers = $metadata->getServers();
        if (count($annotationServers) > 0) {
            $servers = $annotationServers;
        }

        if ($annotationIterations = $metadata->getWorkerIterations()) {
            $this->iterations = $annotationIterations;
        }

        // add servers
        foreach ($servers as $server) {
            $config = split(":", $server);
            $host = $config[0];
            $port = isset($config[1]) ? $config[1] : 4730;

            $this->gearmanWorker->addServer($host, $port);
        }

        // register functions
        foreach ($metadata->getJobs() as $job) {
            $this->gearmanWorker->addFunction(sprintf("%s:%s", $metadata->getWorkerSlug(), $job->name), array($worker, $job->name));
        }

        $this->workersCount++;
    }

    /**
     * Execute all registered workers
     *
     * @param OutputInterface $output
     */
    public function execute(OutputInterface $output)
    {
        if ($this->workersCount > 0) {
            $output->writeln('GearmanWorker <comment>waiting</comment> for job ...');
            $iteration = 0;
            while ($this->gearmanWorker->work() && ++$iteration < $this->iterations) {
                if ($this->gearmanWorker->returnCode() != GEARMAN_SUCCESS) {
                    $output->writeln('GearmanWorker return <comment>code</comment>: <info>['.$this->gearmanWorker->returnCode().']</info>');
                    break;
                }
            }
            $output->writeln('GearmanWorker <comment>exit</comment>');
        }
    }
}
