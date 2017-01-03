<?php

namespace Sandbox\AdminApiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Sandbox\ApiBundle\Traits\DoorAccessTrait;

class SetRoomOrderCommand extends ContainerAwareCommand
{
    use DoorAccessTrait;

    protected function configure()
    {
        $this->setName('RoomOrder:Set')
            ->setDescription('Remove room order in door access')
            ->addArgument('base', InputArgument::REQUIRED, 'Server Address')
            ->addArgument('userArray', InputArgument::REQUIRED, 'Array of user IDs')
            ->addArgument('roomDoors', InputArgument::REQUIRED, 'Array of door IDs')
            ->addArgument('accessNo', InputArgument::REQUIRED, 'Access Number')
            ->addArgument('startDate', InputArgument::REQUIRED, 'Start Date')
            ->addArgument('endDate', InputArgument::REQUIRED, 'End Date');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $arguments = $input->getArguments();
        $base = $arguments['base'];
        $userArray = $arguments['userArray'];
        $roomDoors = $arguments['roomDoors'];
        $accessNo = $arguments['accessNo'];
        $startDate = $arguments['startDate'];
        $endDate = $arguments['endDate'];

        try {
            $this->setRoomOrderAccessIfUserArray(
                $base,
                $userArray,
                $roomDoors,
                $accessNo,
                $startDate,
                $endDate
            );
        } catch (\Exception $e) {
            error_log('Set door access went wrong!');
        }
    }
}
