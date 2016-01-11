<?php

namespace Sandbox\AdminApiBundle\Command;

use Sandbox\ApiBundle\Constants\ProductOrderMessage;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Traits\ProductOrderNotification;
use Sandbox\ApiBundle\Constants\BundleConstants;

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
        $startOrders = $this->getRepo('Order\ProductOrder')
            ->getOfficeStartSoonOrders(
                $now,
                $workspaceTime
            );

        $endOrders = $this->getRepo('Order\ProductOrder')
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
        $startOrders = $this->getRepo('Order\ProductOrder')
            ->getWorkspaceStartSoonOrders(
                $now,
                $workspaceTime
            );

        $endOrders = $this->getRepo('Order\ProductOrder')
            ->getWorkspaceEndSoonOrders(
                $now,
                $workspaceTime
            );

        if (!empty($startOrders)) {
            $this->sendXmppProductOrderNotification(
                null,
                array(),
                ProductOrder::ACTION_START,
                null,
                $startOrders,
                ProductOrderMessage::WORKSPACE_START_MESSAGE
            );
        }

        if (!empty($endOrders)) {
            $this->sendXmppProductOrderNotification(
                null,
                array(),
                ProductOrder::ACTION_END,
                null,
                $endOrders,
                ProductOrderMessage::WORKSPACE_END_MESSAGE
            );
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
