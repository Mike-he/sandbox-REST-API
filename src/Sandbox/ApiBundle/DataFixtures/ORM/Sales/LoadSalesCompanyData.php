<?php

namespace Sandbox\ApiBundle\DataFixtures\ORM\Sales;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompany;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompanyServiceInfos;

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

        $scs1 = new SalesCompanyServiceInfos();
        $scs1->setCompany($sc1);
        $scs1->setRoomTypes('office');
        $scs1->setServiceFee(10);

        $scs2 = new SalesCompanyServiceInfos();
        $scs2->setCompany($sc1);
        $scs2->setRoomTypes('meeting');
        $scs2->setServiceFee(10);

        $scs3 = new SalesCompanyServiceInfos();
        $scs3->setCompany($sc1);
        $scs3->setRoomTypes('longterm');
        $scs3->setServiceFee(10);
        $scs3->setCollectionMethod(SalesCompanyServiceInfos::COLLECTION_METHOD_SALES);
        $scs3->setDrawer(SalesCompanyServiceInfos::DRAWER_SALES);
        $scs3->setInvoicingSubjects('开票科目');

        $manager->persist($sc1);

        $manager->persist($scs1);
        $manager->persist($scs2);
        $manager->persist($scs3);

        $manager->flush();
    }

    public function getOrder()
    {
        return 1;
    }
}
