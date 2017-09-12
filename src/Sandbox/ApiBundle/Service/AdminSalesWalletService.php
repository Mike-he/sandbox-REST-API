<?php

namespace Sandbox\ApiBundle\Service;

use Sandbox\ApiBundle\Entity\Finance\FinanceSalesWalletFlow;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AdminSalesWalletService
{
    private $container;
    private $doctrine;
    private $user;

    public function __construct(
        ContainerInterface $container
    ) {
        $this->container = $container;
        $this->doctrine = $container->get('doctrine');

        $token = $this->container->get('security.token_storage')->getToken();
        $this->user = isset($token) ? $token->getUser() : null;
    }

    /**
     * @param $title
     * @param $amount
     * @param $companyId
     * @param $orderNumber
     */
    public function generateSalesWalletFlows(
        $title,
        $amount,
        $companyId,
        $orderNumber = null,
        $walletAmount = null
    ) {
        $em = $this->doctrine->getManager();

        $flow = new FinanceSalesWalletFlow();
        $flow->setCompanyId($companyId);
        $flow->setTitle($title);
        $flow->setChangeAmount($amount);
        $flow->setWalletTotalAmount($walletAmount);
        $flow->setOrderNumber($orderNumber);
        $em->persist($flow);

        $em->flush();
    }
}
