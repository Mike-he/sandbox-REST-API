<?php

namespace Sandbox\AdminApiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Traits\ProductOrderNotification;

class OrderNotificationCommand extends ContainerAwareCommand
{
    use ProductOrderNotification;
    const BUNDLE = 'SandboxApiBundle';

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

        $startOrders = $this->getContainer()
            ->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getStartSoonOrders(
                $now, $workspaceTime
            );

        $endOrders = $this->getContainer()
            ->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getEndSoonOrders(
                $now,
                $workspaceTime,
                $officeTime,
                $allowedTime
            );

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
