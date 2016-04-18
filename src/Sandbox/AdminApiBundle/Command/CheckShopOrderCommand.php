<?php

namespace Sandbox\AdminApiBundle\Command;

//use Sandbox\ApiBundle\Traits\CurlUtil;
use Sandbox\ApiBundle\Entity\Shop\ShopOrder;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckShopOrderCommand extends ContainerAwareCommand
{
    //    use CurlUtil;

    const HTTP_STATUS_OK = 200;

    protected function configure()
    {
        $this->setName('check:shop')
            ->setDescription('Set order status to cancelled depending on current date and time')
            ->addArgument('my_argument', InputArgument::OPTIONAL, 'Argument description');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setShopOrderStatusCancelled();
    }

    /**
     * Set status cancelled and restock inventory.
     */
    private function setShopOrderStatusCancelled()
    {
        $orders = $this->getContainer()
            ->get('doctrine')
            ->getRepository('SandboxApiBundle:Shop\ShopOrder')
            ->getUnpaidShopOrders();

        $now = new \DateTime();
        $em = $this->getContainer()->get('doctrine')->getManager();
        foreach ($orders as $order) {
            $inventoryData = $this->getContainer()
                ->get('doctrine')
                ->getRepository('SandboxApiBundle:Shop\ShopOrderProduct')
                ->getShopOrderProductInventory($order->getId());

            foreach ($inventoryData as $data) {
                $data['item']->setInventory($data['inventory'] + $data['amount']);
            }

            $order->setStatus(ShopOrder::STATUS_CANCELLED);
            $order->setCancelledDate($now);
            $order->setModificationDate($now);
            $em->flush();
        }
    }
}
