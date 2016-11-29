<?php

namespace Sandbox\AdminApiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitializeDatabaseCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('sandbox:api-bundle:initialize:database')
            ->setDescription('initialize database');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filename = 'app/sql/createView.sql';
        $em = $this->getContainer()->get('doctrine')->getManager();

        $sql = file_get_contents($filename);  // Read file contents
        $em->getConnection()->exec($sql);  // Execute native SQL

        $em->flush();

        $output->writeln('Initial Success!');
    }
}
