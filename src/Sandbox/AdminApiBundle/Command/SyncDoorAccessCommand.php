<?php

namespace Sandbox\AdminApiBundle\Command;

use Doctrine\ORM\EntityManager;
use Sandbox\ApiBundle\Entity\Product\Product;
use Sandbox\ApiBundle\Entity\Room\Room;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Sandbox\ApiBundle\Traits\DoorAccessTrait;

class SyncDoorAccessCommand extends ContainerAwareCommand
{
    use DoorAccessTrait;

    protected function configure()
    {
        $this->setName('sandbox:api-bundle:sync:DoorAccess')
            ->setDescription('Sync user card and room order in door access')
            ->addArgument('userId', InputArgument::REQUIRED, 'user ID')
            ->addArgument('orderId', InputArgument::REQUIRED, 'order ID')
            ->addArgument('type', InputArgument::REQUIRED, 'type: order or lease');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $arguments = $input->getArguments();
        $userId = $arguments['userId'];
        $orderId = $arguments['orderId'];
        $type = $arguments['type'];

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();

        $user = $em->getRepository('SandboxApiBundle:User\User')->find($userId);
        $cardNo = $user->getCardNo();

        if ('order' == $type) {
            $order = $em->getRepository('SandboxApiBundle:Order\ProductOrder')->find($orderId);

            $accessNo = $order->getId();
            $start = $order->getStartDate();
            $end = $order->getEndDate();

            $product = $order->getProduct();
        } elseif ('lease' == $type) {
            $lease = $em->getRepository('SandboxApiBundle:Lease\Lease')->find($orderId);

            $start = $lease->getStartDate();
            $end = $lease->getEndDate();
            $accessNo = $lease->getAccessNo();

            $product = $lease->getProduct();
        }

        /** @var Product $product */
        $room = $product->getRoom();

        $roomDoors = $em
            ->getRepository('SandboxApiBundle:Room\RoomDoors')
            ->findBy(['room' => $room]);

        $building = $room->getBuilding();

        $base = $building->getServer();

        try {
            $this->setEmployeeCardForOneBuilding(
                $base,
                $userId,
                $cardNo
            );
            sleep(10);
            $userArray = [
                ['empid' => "$userId"],
            ];

            $this->setRoomOrderAccessIfUserArray(
                $base,
                $userArray,
                $roomDoors,
                $accessNo,
                $start,
                $end
            );
        } catch (\Exception $e) {
            error_log('Set card and room door access went wrong!');
        }
    }
}
