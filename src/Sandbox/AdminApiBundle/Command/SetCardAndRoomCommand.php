<?php

namespace Sandbox\AdminApiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Sandbox\ApiBundle\Traits\DoorAccessTrait;
use Sandbox\ApiBundle\Constants\BundleConstants;

class SetCardAndRoomCommand extends ContainerAwareCommand
{
    use DoorAccessTrait;

    protected function configure()
    {
        $this->setName('CardAndRoom:Set')
            ->setDescription('Set user card and room order in door access')
            ->addArgument('base', InputArgument::REQUIRED, 'Server Address')
            ->addArgument('userId', InputArgument::REQUIRED, 'user ID')
            ->addArgument('cardNo', InputArgument::REQUIRED, 'user card number')
            ->addArgument('roomDoors', InputArgument::REQUIRED, 'array of door IDs')
            ->addArgument('order', InputArgument::REQUIRED, 'ProductOrder Object');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $arguments = $input->getArguments();
        $base = $arguments['base'];
        $userId = $arguments['userId'];
        $cardNo = $arguments['cardNo'];
        $roomDoors = $arguments['roomDoors'];
        $order = $arguments['order'];

        try {
            $this->setEmployeeCardForOneBuilding(
                $base,
                $userId,
                $cardNo
            );
            sleep(1);
            $userArray = [
                ['empid' => "$userId"],
            ];
            $this->setRoomOrderAccessIfUserArray(
                $base,
                $userArray,
                $roomDoors,
                $order
            );
        } catch (\Exception $e) {
            error_log('Set card and room door access went wrong!');
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
