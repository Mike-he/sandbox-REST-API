<?php

namespace Sandbox\ApiBundle\DataFixtures\ORM\User;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompany;

class LoadSalesCompanyData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $date = new \DateTime('now');

        $sc1 = new SalesCompany();
        $sc1->setName('展现创合');
        $sc1->setEmail('sandbox@sandbox3.cn');
        $sc1->setPhone('05012312312');
        $sc1->setAddress('浦东新区祖冲之路2290, 1#楼');
        $sc1->setDescription('咖啡销售');
        $sc1->setApplicantName('sandbox');
        $sc1->setCreationDate($date);
        $sc1->setModificationDate($date);
        $this->addReference('sales-company-sandbox', $sc1);

        $manager->persist($sc1);

        $manager->flush();
    }

    public function getOrder()
    {
        return 1;
    }
}
