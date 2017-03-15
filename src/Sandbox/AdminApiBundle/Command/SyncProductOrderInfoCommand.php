<?php

namespace Sandbox\AdminApiBundle\Command;

use Sandbox\ApiBundle\Entity\Order\ProductOrderInfo;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncProductOrderInfoCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('sandbox:api-bundle:sync_order_info')
            ->setDescription('Sync Product Order Info');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        $orders = $em->getRepository('SandboxApiBundle:Order\ProductOrder')->findAll();

        foreach ($orders as $order) {
            $orderId = $order->getId();
            $productOrderInfo = $em->getRepository('SandboxApiBundle:Order\ProductOrderInfo')
                ->findOneBy(array('orderId' => $orderId));

            if (is_null($productOrderInfo)) {
                $productOrderInfo = new ProductOrderInfo();
                $productOrderInfo->setOrderId($orderId);
                $productOrderInfo->setProductInfo($order->getProductInfo());

                $em->persist($productOrderInfo);
            }
        }
        $em->flush();

        $output->writeln('Sync Success!');
    }
}
