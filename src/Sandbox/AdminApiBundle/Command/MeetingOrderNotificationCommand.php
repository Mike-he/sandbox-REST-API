<?php

namespace Sandbox\AdminApiBundle\Command;

use Sandbox\ApiBundle\Constants\ProductOrderMessage;
use Sandbox\ApiBundle\Entity\Room\Room;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Traits\ProductOrderNotification;

class MeetingOrderNotificationCommand extends ContainerAwareCommand
{
    use ProductOrderNotification;

    protected function configure()
    {
        $this->setName('meeting:notification')
            ->setDescription('Send notifications 10 minutes before start and end of an order')
            ->addArgument('my_argument', InputArgument::OPTIONAL, 'Argument description');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $now = new \DateTime();
        $meetingTime = clone $now;
        $meetingTime->modify('+15 minutes');

        $this->sendMessages(
            $now,
            $meetingTime,
            Room::TYPE_MEETING,
            ProductOrderMessage::MEETING_START_MESSAGE,
            ProductOrderMessage::MEETING_END_MESSAGE
        );

        $this->sendMessages(
            $now,
            $meetingTime,
            Room::TYPE_OTHERS,
            ProductOrderMessage::OTHERS_START_MESSAGE,
            ProductOrderMessage::OTHERS_END_MESSAGE
        );
    }

    private function sendMessages(
        $now,
        $meetingTime,
        $type,
        $startMessage,
        $endMessage
    ) {
        $startOrders = $this->getContainer()
            ->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getMeetingStartSoonOrders($now, $meetingTime, $type);

        $endOrders = $this->getContainer()
            ->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getMeetingEndSoonOrders($now, $meetingTime, $type);

        if (!empty($startOrders)) {
            $this->sendXmppProductOrderNotification(
                null,
                null,
                ProductOrder::ACTION_START,
                null,
                $startOrders,
                $startMessage
            );
        }

        if (!empty($endOrders)) {
            $this->sendXmppProductOrderNotification(
                null,
                null,
                ProductOrder::ACTION_END,
                null,
                $endOrders,
                $endMessage
            );
        }
    }
}
