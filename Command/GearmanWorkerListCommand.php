<?php

namespace Ulabox\Bundle\GearmanBundle\Command;

use Ulabox\Bundle\GearmanBundle\Manager\GearmanManager;
use Ulabox\Bundle\GearmanBundle\Metadata\ClassMetadata;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Metadata\MetadataFactory;

/**
 * Worker list command.
 *
 * @author Ivannis Suárez Jérez <ivannis.suarez@gmail.com>
 */
class GearmanWorkerListCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('gearman:worker:list')
        ->setDescription('Show worker list')
        ->setHelp(<<<EOT
The command <info>{$this->getName()}</info> show all workers registered in the system.
EOT
    );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write("Loading <comment>workers</comment> \n");

        $index = 0;
        $workers = $this->getManager()->getWorkers();
        if (!$workers) {
            $output->write("<info>There is no workers <info>\n");
        } else {
            foreach ($workers as $worker) {
                /* @var $metadata ClassMetadata */
                $metadata = $this->getMetadataFactory()->getMetadataForClass(get_class($worker))->getOutsideClassMetadata();

                $output->writeln('<info>    Worker: <info><comment>'.$metadata->getWorkerSlug().'</comment>');
                $output->writeln('<info>        Jobs: </info>');
                foreach ($metadata->getJobs() as $job) {
                    $output->writeln('<comment>            - </comment><comment>'.str_replace('_', ' ', $job->name).'</comment>');
                }
            }
        }
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
