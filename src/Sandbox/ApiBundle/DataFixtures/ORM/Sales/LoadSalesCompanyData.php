<?php

namespace Sandbox\ApiBundle\DataFixtures\ORM\Sales;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sandbox\ApiBundle\Entity\Room\Room;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompany;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompanyServiceInfos;

class LoadSalesCompanyData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $sc1 = new SalesCompany();
        $sc1->setName('展现创合');
        $sc1->setContacterEmail('sandbox@sandbox3.cn');
        $sc1->setPhone('05012312312');
        $sc1->setAddress('浦东新区祖冲之路2290, 1#楼');
        $sc1->setDescription('咖啡销售');
        $sc1->setContacter('sandbox');
        $sc1->setContacterPhone('13010103232');
        $this->addReference('sales-company-sandbox', $sc1);

        $scs1 = new SalesCompanyServiceInfos();
        $scs1->setCompany($sc1);
        $scs1->setRoomTypes(Room::TYPE_MEETING);
        $scs1->setServiceFee(10);

        $scs2 = new SalesCompanyServiceInfos();
        $scs2->setCompany($sc1);
        $scs2->setRoomTypes(Room::TYPE_MEETING);
        $scs2->setServiceFee(10);

        $scs3 = new SalesCompanyServiceInfos();
        $scs3->setCompany($sc1);
        $scs3->setRoomTypes(Room::TYPE_LONG_TERM);
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
