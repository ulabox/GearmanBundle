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
     * Workers count
     *
     * @var integer
     */
    protected $workersCount;

    /**
     * The metadata factory
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
    public function __construct(MetadataFactory $metadataFactory)
    {
        $this->metadataFactory = $metadataFactory;
        $this->gearmanWorker = new \GearmanWorker();
        $this->workersCount = 0;
    }

    /**
     * Add worker to gearman
     *
     * @param WorkerInterface $worker
     */
    public function addWorker(WorkerInterface $worker)
    {
        /* @var $metadata ClassMetadata */
        $metadata = $this->metadataFactory->getMetadataForClass(get_class($worker))->getOutsideClassMetadata();

        // add servers
        foreach ($metadata->getServers() as $server) {
            $this->gearmanWorker->addServer($server);
        }

        // register functions
        foreach ($metadata->getJobs() as $job) {
            $this->gearmanWorker->addFunction(sprintf("%s:%s", $metadata->getWorkerSlug(), $job->name), array($worker, $job->name));
        }

        $this->workersCount++;
        $this->iterations = $metadata->getWorkerIterations();
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
