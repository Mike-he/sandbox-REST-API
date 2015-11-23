<?php

namespace Sandbox\AdminApiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Traits\ProductOrderNotification;

class MeetingOrderNotificationCommand extends ContainerAwareCommand
{
    use ProductOrderNotification;
    const BUNDLE = 'SandboxApiBundle';

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

        $startOrders = $this->getRepo('Order\ProductOrder')
            ->getMeetingStartSoonOrders($now, $meetingTime);

        $endOrders = $this->getRepo('Order\ProductOrder')
            ->getMeetingEndSoonOrders($now, $meetingTime);

        if (!empty($startOrders)) {
            $this->sendXmppProductOrderNotification(
                null,
                null,
                array(),
                ProductOrder::ACTION_START,
                null,
                $startOrders
            );
        }

        if (!empty($endOrders)) {
            $this->sendXmppProductOrderNotification(
                null,
                null,
                array(),
                ProductOrder::ACTION_END,
                null,
                $endOrders
            );
        }
    }

    protected function getRepo(
        $repo
    ) {
        return $this->getContainer()
            ->get('doctrine')
            ->getRepository(self::BUNDLE.':'.$repo);
    }

    protected function getGlobals()
    {
        // get globals
        return $this->getContainer()
            ->get('twig')
            ->getGlobals();
    }
}
