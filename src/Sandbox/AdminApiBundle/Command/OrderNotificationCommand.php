<?php

namespace Sandbox\AdminApiBundle\Command;

use Proxies\__CG__\Sandbox\ApiBundle\Entity\Room\Room;
use Sandbox\ApiBundle\Constants\ProductOrderMessage;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Traits\ProductOrderNotification;

class OrderNotificationCommand extends ContainerAwareCommand
{
    use ProductOrderNotification;

    protected function configure()
    {
        $this->setName('order:notification')
            ->setDescription('Send notifications a day or 7 days before start and end of an order')
            ->addArgument('my_argument', InputArgument::OPTIONAL, 'Argument description');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $now = new \DateTime();
        $workspaceTime = clone $now;
        $workspaceTime->modify('+9 hours');
        $officeTime = clone $workspaceTime;
        $officeTime->modify('+7 days');
        $allowedTime = clone $workspaceTime;
        $allowedTime->modify('+6 days');

        $this->sendOfficeNotification(
            $now,
            $workspaceTime,
            $officeTime,
            $allowedTime
        );

        $this->sendWorkspaceNotification(
            $now,
            $workspaceTime
        );
    }

    /**
     * @param $now
     * @param $workspaceTime
     * @param $officeTime
     * @param $allowedTime
     */
    private function sendOfficeNotification(
        $now,
        $workspaceTime,
        $officeTime,
        $allowedTime
    ) {
        $startOrders = $this->getContainer()
            ->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getOfficeStartSoonOrders(
                $now,
                $workspaceTime
            );

        $endOrders = $this->getContainer()
            ->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getOfficeEndSoonOrders(
                $now,
                $officeTime,
                $allowedTime
            );

        if (!empty($startOrders)) {
            $this->sendXmppProductOrderNotification(
                null,
                array(),
                ProductOrder::ACTION_START,
                null,
                $startOrders,
                ProductOrderMessage::OFFICE_START_MESSAGE
            );
        }

        if (!empty($endOrders)) {
            $this->sendXmppProductOrderNotification(
                null,
                array(),
                ProductOrder::ACTION_END,
                null,
                $endOrders,
                ProductOrderMessage::OFFICE_END_MESSAGE
            );
        }
    }

    /**
     * @param $now
     * @param $workspaceTime
     * @param $officeTime
     * @param $allowedTime
     */
    private function sendWorkspaceNotification(
        $now,
        $workspaceTime
    ) {
        $fixedStartOrders = $this->getContainer()
            ->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getWorkspaceStartSoonOrders(
                $now,
                $workspaceTime,
                Room::TYPE_FIXED
            );

        $fixedEndOrders = $this->getContainer()
            ->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getWorkspaceEndSoonOrders(
                $now,
                $workspaceTime,
                Room::TYPE_FIXED
            );

        $flexStartOrders = $this->getContainer()
            ->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getWorkspaceStartSoonOrders(
                $now,
                $workspaceTime,
                Room::TYPE_FLEXIBLE
            );

        $flexEndOrders = $this->getContainer()
            ->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getWorkspaceEndSoonOrders(
                $now,
                $workspaceTime,
                Room::TYPE_FLEXIBLE
            );

        if (!empty($fixedStartOrders)) {
            $this->sendXmppProductOrderNotification(
                null,
                array(),
                ProductOrder::ACTION_START,
                null,
                $fixedStartOrders,
                ProductOrderMessage::FIXED_START_MESSAGE
            );
        }

        if (!empty($fixedEndOrders)) {
            $this->sendXmppProductOrderNotification(
                null,
                array(),
                ProductOrder::ACTION_END,
                null,
                $fixedEndOrders,
                ProductOrderMessage::FIXED_END_MESSAGE
            );
        }

        if (!empty($flexStartOrders)) {
            $this->sendXmppProductOrderNotification(
                null,
                array(),
                ProductOrder::ACTION_START,
                null,
                $flexStartOrders,
                ProductOrderMessage::FLEXIBLE_START_MESSAGE
            );
        }

        if (!empty($flexEndOrders)) {
            $this->sendXmppProductOrderNotification(
                null,
                array(),
                ProductOrder::ACTION_END,
                null,
                $fixedEndOrders,
                ProductOrderMessage::FLEXIBLE_END_MESSAGE
            );
        }
    }
}
