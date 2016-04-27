<?php

namespace Sandbox\AdminApiBundle\Command;

//use Sandbox\ApiBundle\Traits\CurlUtil;
use Sandbox\ApiBundle\Entity\Shop\ShopOrder;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckStatusCommand extends ContainerAwareCommand
{
    //    use CurlUtil;

    const HTTP_STATUS_OK = 200;

    protected function configure()
    {
        $this->setName('check:status')
            ->setDescription('Set order status to completed or cancelled depending on current date and time')
            ->addArgument('my_argument', InputArgument::OPTIONAL, 'Argument description');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setShopOrderStatusCancelled();

        // set product order status cancelled
        $this->getContainer()
            ->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->setStatusCancelled();

        // set room product status visible false
        $this->getContainer()
            ->get('doctrine')
            ->getRepository('SandboxApiBundle:Product\Product')
            ->setVisibleFalse();

        // get paid product order and set status completed
        $orders = $this->getContainer()
            ->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getStatusPaid();

        if (!empty($orders)) {
            foreach ($orders as $order) {
                $order->setStatus('completed');
                $order->setModificationDate(new \DateTime('now'));
                $em = $this->getContainer()->get('doctrine')->getManager();
                $em->flush();

                //TODO: VIP Membership Module
//                $membershipBindId = $order->getMembershipBindId();
//                if (!is_null($membershipBindId)) {
//                    $this->postAccountUpgrade($order, $membershipBindId);
//                }
            }
        }

        // set event order status cancelled & delete event registrations
        $this->getContainer()
            ->get('doctrine')
            ->getRepository('SandboxApiBundle:Event\EventOrder')
            ->setStatusCancelled();

        $this->getContainer()
            ->get('doctrine')
            ->getRepository('SandboxApiBundle:Event\EventRegistration')
            ->deleteEventRegistrations();

        // set event order status completed
        $this->getContainer()
            ->get('doctrine')
            ->getRepository('SandboxApiBundle:Event\EventOrder')
            ->setStatusCompleted();
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

//    private function postAccountUpgrade(
//        $order,
//        $membershipBindId
//    ) {
//        $userId = $order->getUserId();
//        $tradeId = $order->getOrderNumber();
//
//        $twig = $this->getContainer()->get('twig');
//        $globals = $twig->getGlobals();
//        // CRM API URL
//        $apiUrl = $globals['crm_api_url'].
//            $globals['crm_api_admin_user_account_upgrade'];
//        $apiUrl = preg_replace('/{userId}.*?/', "$userId", $apiUrl);
//
//        $content = [
//            'product_id' => $membershipBindId,
//            'trade_id' => $tradeId,
//        ];
//        $content = json_encode($content);
//        $key = $globals['sandbox_auth_key'];
//
//        $contentMd5 = md5($content.$key);
//        $contentMd5 = strtoupper($contentMd5);
//
//        // init curl
//        $ch = curl_init($apiUrl);
//        $response = $this->callAPI(
//            $ch,
//            'POST',
//            array('Sandbox-Auth: '.$contentMd5),
//            $content);
//
//        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//        if ($httpCode != self::HTTP_STATUS_OK) {
//            return;
//        }
//    }
}
