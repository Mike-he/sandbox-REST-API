<?php

namespace Sandbox\AdminApiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Sandbox\ApiBundle\Traits\DoorAccessTrait;
use Sandbox\ApiBundle\Constants\BundleConstants;

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
            ->addArgument('order', InputArgument::REQUIRED, 'ProductOrder Object');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $arguments = $input->getArguments();
        $base = $arguments['base'];
        $userArray = $arguments['userArray'];
        $roomDoors = $arguments['roomDoors'];
        $order = $arguments['order'];

        try {
            $this->setRoomOrderAccessIfUserArray(
                $base,
                $userArray,
                $roomDoors,
                $order
            );
        } catch (\Exception $e) {
            error_log('Set door access went wrong!');
        }
    }

    protected function getRepo(
        $repo
    ) {
        return $this->getContainer()
            ->get('doctrine')
            ->getRepository(BundleConstants::BUNDLE.':'.$repo);
    }

    protected function getGlobals()
    {
        // get globals
        return $this->getContainer()
            ->get('twig')
            ->getGlobals();
    }
}
