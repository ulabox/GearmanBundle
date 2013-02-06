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

class GearmanClientExecuteCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('gearman:client:execute')
        ->setDescription('Execute clients')
        ->addOption('client', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'The directory or file to load clients from.')
        ->addOption('method', null, InputOption::VALUE_REQUIRED, 'The client method', 'doBackgroundJob')
        ->addOption('params', null, InputOption::VALUE_REQUIRED, 'The parameters passed to the worker', '{}')
        ->setHelp(<<<EOT
The <info>{$this->getName()}</info> command execute clients from your bundles:

<info>./app/console gearman:client:execute</info>

You can also optionally specify the path to client with the <info>--client</info> option:

  <info>./app/console doctrine:client:execute --client=client-namespace --client=other-client-namespace</info>

EOT
    );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write("Starting <comment>clients</comment> \n");
        $method = in_array(
            $input->getOption('method'),
            array(
              'doBackgroundJob',
              'doHighJob',
              'doHighBackgroundJob',
              'doLowJob',
              'doLowBackgroundJob',
              'doNormalJob',
              'addTask',
              'addTaskBackground',
              'addTaskHigh',
              'addTaskHighBackground',
              'addTaskLow',
              'addTaskLowBackground',
              'runTasks'
            )
        ) ? $input->getOption('method') : 'doBackgroundJob';

        $params = json_decode($input->getOption('params'), true) !== null ? $input->getOption('params') : '';

        $index = 0;
        if ($clients = $input->getOption('client')) {
            foreach ($clients as $name) {
                $namespace = substr($name, 0, strrpos($name, ':'));
                $job = substr($name, strrpos($name, ':') + 1);

                if ($client = $this->getManager()->getClient($namespace)) {
                    $output->writeln('<comment>    > </comment><info>executing ['.++$index.'] '.$name.'</info>');
                    $client->{$method}($job, $params);
                } else {
                    $output->write("<info>There is no client with name</info> <comment>".$name."</comment> \n");
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
