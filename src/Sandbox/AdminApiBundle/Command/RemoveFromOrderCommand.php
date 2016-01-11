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
            ->addArgument('orderId', InputArgument::REQUIRED, 'order ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $arguments = $input->getArguments();
        $base = $arguments['base'];
        $userArray = $arguments['userArray'];
        $orderId = $arguments['orderId'];

        try {
            $this->deleteEmployeeToOrder(
                $base,
                $orderId,
                $userArray
            );
        } catch (\Exception $e) {
            error_log('Remove user from door access went wrong!');
        }
    }
}
