<?php

namespace Sandbox\AdminApiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckStatusCommand extends ContainerAwareCommand
{
    const HTTP_STATUS_OK = 200;

    protected function configure()
    {
        $this->setName('check:status')
            ->setDescription('Set order status to completed or cancelled depending on current date and time')
            ->addArgument('my_argument', InputArgument::OPTIONAL, 'Argument description');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()
            ->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->setStatusCancelled();

        $orders = $this->getContainer()
            ->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getStatusPaid();

        $twig = $this->getContainer()->get('twig');
        $globals = $twig->getGlobals();
        $apiUrl = $globals['crm_api_url'].
            $globals['crm_api_admin_user_consume'];

        if (!empty($orders)) {
            foreach ($orders as $order) {
                $userId = $order->getUserId();
                $price = $order->getPrice();
                $orderNumber = $order->getOrderNumber();
                $order->setStatus('completed');
                $em = $this->getContainer()->get('doctrine')->getManager();
                $em->persist($order);
                $em->flush();

                $apiUrl = preg_replace('/{userId}.*?/', "$userId", $apiUrl);
                $content = [
                    'amount' => $price,
                    'trade_id' => $orderNumber,
                ];
                $content = json_encode($content);
                $key = $globals['sandbox_auth_key'];

                $contentMd5 = md5($content.$key);
                $contentMd5 = strtoupper($contentMd5);

                $ch = curl_init($apiUrl);
                $response = $this->getContainer()->get('curl_util')->callInternalAPI($ch, 'POST', $contentMd5, $content);

                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                if ($httpCode != self::HTTP_STATUS_OK) {
                    return;
                }
            }
        }
    }
}