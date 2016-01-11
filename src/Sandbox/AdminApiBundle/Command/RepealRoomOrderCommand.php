<?php

namespace Sandbox\AdminApiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Sandbox\ApiBundle\Traits\DoorAccessTrait;
use Sandbox\ApiBundle\Constants\BundleConstants;

class RepealRoomOrderCommand extends ContainerAwareCommand
{
    use DoorAccessTrait;

    protected function configure()
    {
        $this->setName('RoomOrder:Repeal')
            ->setDescription('Remove room order in door access')
            ->addArgument('base', InputArgument::REQUIRED, 'Server Address')
            ->addArgument('orderId', InputArgument::REQUIRED, 'order ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $arguments = $input->getArguments();
        $base = $arguments['base'];
        $orderId = $arguments['orderId'];

        try {
            $this->repealRoomOrder(
                $base,
                $orderId
            );
        } catch (\Exception $e) {
            error_log('remove door access went wrong!');
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
