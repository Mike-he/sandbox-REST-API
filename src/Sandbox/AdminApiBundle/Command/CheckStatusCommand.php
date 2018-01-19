<?php

namespace Sandbox\AdminApiBundle\Command;

//use Sandbox\ApiBundle\Traits\CurlUtil;
use Doctrine\ORM\EntityManager;
use Sandbox\ApiBundle\Entity\Event\Event;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Entity\Service\Service;
use Sandbox\ApiBundle\Traits\SetStatusTrait;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckStatusCommand extends ContainerAwareCommand
{
    use SetStatusTrait;

    protected function configure()
    {
        $this->setName('check:status')
            ->setDescription('Set order status to completed or cancelled depending on current date and time')
            ->addArgument('my_argument', InputArgument::OPTIONAL, 'Argument description');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();

        $this->checkProductOrders($em);
        $em->flush();

        $this->setInvoiceForProductOrders($em);

        $this->checkEventOrders($em);

        $this->checkServiceStatus($em);

        $em->flush();
    }

    /**
     * @param EntityManager $em
     */
    private function setInvoiceForProductOrders($em)
    {
        // get paid product order and set status completed
        $orders = $em->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getInvoiceOrders();

        foreach ($orders as $order) {
            $this->setProductOrderInvoice($order);
        }
    }

    /**
     * check and set status for product order.
     *
     * @param EntityManager $em
     */
    private function checkProductOrders(
        $em
    ) {
        // set product order status cancelled
        $em->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->setStatusCancelled();

        $em->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->setPreOrderStatusCancelled();

        // set room product status visible false
        $em->getRepository('SandboxApiBundle:Product\Product')
            ->setVisibleFalse();

        // get unpaid preorder product orders
        // removed for preorder

        //$twig = $this->getContainer()->get('twig');
        //$globals = $twig->getGlobals();
        //$modifyTime = $globals['time_for_preorder_cancel'];

//        $preorders = $this->getContainer()
//            ->get('doctrine')
//            ->getRepository('SandboxApiBundle:Order\ProductOrder')
//            ->getUnpaidPreOrders();

//        foreach ($preorders as $preorder) {
//            $this->checkPreOrders($preorder, $modifyTime);
//        }

        // get paid product order and set status completed
        $orders = $em->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getStatusPaid();

        foreach ($orders as $order) {
            $this->setProductOrderStatusCompleted($order);
        }
    }

    private function checkPreOrders(
        $order,
        $modifyTime
    ) {
        $now = new \DateTime();
        $start = $order->getStartDate();
        $creationTime = $order->getCreationDate();

        if ($start > $now) {
            $remainingTime = $start->diff($creationTime);
            $days = $remainingTime->d;

            if ($days > 0) {
                $endTime = clone $creationTime;
                $endTime->modify($modifyTime);

                if ($now >= $endTime) {
                    $order->setStatus(ProductOrder::STATUS_CANCELLED);
                    $order->setCancelledDate($now);
                    $order->setModificationDate($now);
                }
            }
        } else {
            $remainingTime = $now->diff($creationTime);
            $minutes = $remainingTime->i;

            $minutes = 4 - $minutes;

            if ($minutes < 0) {
                $order->setStatus(ProductOrder::STATUS_CANCELLED);
                $order->setCancelledDate($now);
                $order->setModificationDate($now);
            }
        }
    }

    /**
     * check and set status for event orders.
     *
     * @param EntityManager $em
     */
    private function checkEventOrders(
        $em
    ) {
        // set event order status cancelled & delete event registrations
        $em->getRepository('SandboxApiBundle:Event\EventRegistration')
            ->deleteEventRegistrations();

        $em->getRepository('SandboxApiBundle:Event\EventOrder')
            ->setStatusCancelled();

        // set event order status completed
        $orders = $em->getRepository('SandboxApiBundle:Event\EventOrder')
            ->getStatusCompleted();

        foreach ($orders as $order) {
            $this->setEventOrderStatusCompleted($order);
        }

        $eventStatus = [
            Event::STATUS_PREHEATING,
            Event::STATUS_REGISTERING,
            Event::STATUS_ONGOING,
        ];
        $events = $em->getRepository('SandboxApiBundle:Event\Event')
            ->findBy(array(
                'isSaved' => false,
                'status' => $eventStatus,
            ));

        foreach ($events as $event) {
            $this->setEventStatus($event);
        }
    }

    /**
     * @param EntityManager $em
     */
    private function checkServiceStatus(
        $em
    ) {
        $status = [
            Service::STATUS_PREHEATING,
            Service::STATUS_ONGOING
        ];

        $services = $em->getRepository('SandboxApiBundle:Service\Service')
            ->findBy([
                'isSaved' => false,
                'status' => $status
            ]);

        foreach ($services as $service) {
            $this->setServiceStatus($service);
        }
    }

//    private function postAccountUpgrade(
//        $order,
//        $membershipBindId
//    ) {
//        $userId = $order->getUserId();
//        $tradeId = $order->getOrderNumber();

//        $twig = $this->getContainer()->get('twig');
//        $globals = $twig->getGlobals();
//        // CRM API URL
//        $apiUrl = $globals['crm_api_url'].
//            $globals['crm_api_admin_user_account_upgrade'];
//        $apiUrl = preg_replace('/{userId}.*?/', "$userId", $apiUrl);

//        $content = [
//            'product_id' => $membershipBindId,
//            'trade_id' => $tradeId,
//        ];
//        $content = json_encode($content);
//        $key = $globals['sandbox_auth_key'];

//        $contentMd5 = md5($content.$key);
//        $contentMd5 = strtoupper($contentMd5);

//        // init curl
//        $ch = curl_init($apiUrl);
//        $response = $this->callAPI(
//            $ch,
//            'POST',
//            array('Sandbox-Auth: '.$contentMd5),
//            $content);

//        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//        if ($httpCode != self::HTTP_STATUS_OK) {
//            return;
//        }
//    }
}
