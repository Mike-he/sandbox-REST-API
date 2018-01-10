<?php

namespace Sandbox\ApiBundle\DataFixtures\ORM\User;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sandbox\ApiBundle\Entity\User\UserCustomer;

class LoadUserCustomerData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $user1 = $this->getReference('user-mike');
        $company = $this->getReference('sales-company-sandbox');

        $uc1 = new UserCustomer();
        $uc1->setUserId($user1->getId());
        $uc1->setPhone($user1->getPhone());
        $uc1->setPhoneCode($user1->getPhoneCode());
        $uc1->setName('test1');
        $uc1->setCompanyId($company->getId());
        $this->addReference('user-customer-1', $uc1);

        $manager->persist($uc1);

        $manager->flush();
    }

    public function getOrder()
    {
        return 18;
    }
}
