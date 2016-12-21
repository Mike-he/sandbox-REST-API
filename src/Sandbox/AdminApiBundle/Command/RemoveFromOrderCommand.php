<?php

namespace Sandbox\AdminApiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Sandbox\ApiBundle\Traits\DoorAccessTrait;

class RemoveFromOrderCommand extends ContainerAwareCommand
{
    use DoorAccessTrait;

    protected function configure()
    {
        $this->setName('RoomOrderUser:Remove')
            ->setDescription('Remove user from room order in door access')
            ->addArgument('base', InputArgument::REQUIRED, 'Server Address')
            ->addArgument('userArray', InputArgument::REQUIRED, 'Array of user IDs')
            ->addArgument('accessNo', InputArgument::REQUIRED, 'accessNo');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $arguments = $input->getArguments();
        $base = $arguments['base'];
        $userArray = $arguments['userArray'];
        $accessNo = $arguments['accessNo'];

        try {
            $this->deleteEmployeeToOrder(
                $base,
                $accessNo,
                $userArray
            );
        } catch (\Exception $e) {
            error_log('Remove user from door access went wrong!');
        }
    }
}
