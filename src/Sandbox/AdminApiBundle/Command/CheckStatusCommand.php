<?php

namespace Sandbox\AdminApiBundle\Command;

//use Sandbox\ApiBundle\Traits\CurlUtil;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Traits\ConsumeTrait;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckStatusCommand extends ContainerAwareCommand
{
    use ConsumeTrait;

    protected function configure()
    {
        $this->setName('check:status')
            ->setDescription('Set order status to completed or cancelled depending on current date and time')
            ->addArgument('my_argument', InputArgument::OPTIONAL, 'Argument description');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
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

                if ($order->isRejected()
                    || (ProductOrder::CHANNEL_ACCOUNT == $order->getPayChannel())
                ) {
                    continue;
                }

                // set invoice amount
                $amount = $this->postConsumeBalance(
                    $order->getUserId(),
                    $order->getDiscountPrice(),
                    $order->getOrderNumber()
                );

                //TODO: VIP Membership Module
//                $membershipBindId = $order->getMembershipBindId();
//                if (!is_null($membershipBindId)) {
//                    $this->postAccountUpgrade($order, $membershipBindId);
//                }
            }
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
