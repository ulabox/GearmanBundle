<?php

namespace Ulabox\Bundle\GearmanBundle\Command;

use Ulabox\Bundle\GearmanBundle\Worker\WorkerExecutor;
use Ulabox\Bundle\GearmanBundle\Manager\GearmanManager;
use Ulabox\Bundle\GearmanBundle\Model\WorkerInterface;
use Ulabox\Bundle\GearmanBundle\Metadata\ClassMetadata;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Metadata\MetadataFactory;

class GearmanWorkerExecuteCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('gearman:worker:execute')
        ->setDescription('Execute workers')
        ->addOption('worker', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'The directory or file to load workers from.')
        ->setHelp(<<<EOT
The <info>{$this->getName()}</info> command execute workers from your bundles:

<info>./app/console gearman:worker:execute</info>

You can also optionally specify the path to worker with the <info>--worker</info> option:

  <info>./app/console doctrine:worker:execute --worker=worker-namespace --worker=other-worker-namespace</info>

EOT
    );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write("Starting <comment>workers</comment> \n");

        $index = 0;
        if ($workers = $input->getOption('worker')) {
            foreach ($workers as $name) {
                if ($worker = $this->getManager()->getWorker($name)) {
                    /* @var $metadata ClassMetadata */
                    $metadata = $this->getMetadataFactory()->getMetadataForClass(get_class($worker))->getOutsideClassMetadata();
                    $output->writeln('<comment>    > </comment><info>loading ['.++$index.'] '.$metadata->getWorkerSlug().'</info>');

                    // add worker to gearman
                    $this->getWorkerExecutor()->addWorker($worker);
                } else {
                    $output->write("<info>There is no worker with name</info> <comment>".$name."</comment> \n");
                }
            }

            $this->getWorkerExecutor()->execute($output);
        } else {
            $workers = $this->getManager()->getWorkers();

            if (count($workers) > 0) {
                foreach ($workers as $worker) {
                    /* @var $metadata ClassMetadata */
                    $metadata = $this->getMetadataFactory()->getMetadataForClass(get_class($worker))->getOutsideClassMetadata();
                    $output->writeln('<comment>    > </comment><info>loading ['.++$index.'] '.$metadata->getWorkerSlug().'</info>');

                    // add worker to gearman
                    $this->getWorkerExecutor()->addWorker($worker);
                }

                $this->getWorkerExecutor()->execute($output);
            } else {
                $output->write("<info>There is no workers <info>\n");
            }
        }
    }

    /**
     * Return the worker executor
     *
     * @return WorkerExecutor
     */
    private function getWorkerExecutor()
    {
        return $this->getContainer()->get('ulabox_gearman.executor.worker');
    }

    /**
     * Return the metadata factory
     *
     * @return MetadataFactory
     */
    private function getMetadataFactory()
    {
        return $this->getContainer()->get('ulabox_gearman.metadata_factory');
    }

    /**
     * Return the gearman manager
     *
     * @return GearmanManager
     */
    private function getManager()
    {
        return $this->getContainer()->get('ulabox_gearman.manager');
    }
}
