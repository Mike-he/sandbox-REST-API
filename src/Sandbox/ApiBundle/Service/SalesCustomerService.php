<?php

namespace Sandbox\ApiBundle\Service;

use Sandbox\ApiBundle\Entity\User\UserCustomer;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SalesCustomerService
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function createCustomer(
        $userId,
        $companyId
    ) {
        $em = $this->container->get('doctrine')->getManager();

        $customer = $em->getRepository('SandboxApiBundle:User\UserCustomer')
            ->findOneBy(array(
                'userId' => $userId,
                'companyId' => $companyId,
            ));

        if ($customer) {
            return ;
        }

        $user = $em->getRepository('SandboxApiBundle:User\User')
            ->find($userId);

        if(!$user) {
            return ;
        }

        $phoneCode = $user->getPhoneCode();
        $phone = $user->getPhone();

        $userProfile = $em->getRepository('SandboxApiBundle:User\UserProfile')
            ->findOneBy(array('userId' => $userId));
        $userName = $userProfile ? $userProfile->getName() : null;

        $customer = new UserCustomer();
        $customer->setName($userName);
        $customer->setUserId($userId);
        $customer->setPhoneCode($phoneCode);
        $customer->setPhone($phone);
        $customer->setCompanyId($companyId);
        $em->persist($customer);

        $em->flush();
    }
}