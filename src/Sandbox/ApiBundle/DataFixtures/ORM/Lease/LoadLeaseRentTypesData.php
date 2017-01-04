<?php

namespace Sandbox\ApiBundle\DataFixtures\ORM\Lease;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sandbox\ApiBundle\Entity\Lease\LeaseRentTypes;

class LoadLeaseRentTypesData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $lrt1 = new LeaseRentTypes();
        $lrt1->setName('水费');
        $lrt1->setNameEn('Water');

        $lrt2 = new LeaseRentTypes();
        $lrt2->setName('电费');
        $lrt2->setNameEn('Electricity');

        $lrt3 = new LeaseRentTypes();
        $lrt3->setName('场地使用费');
        $lrt3->setNameEn('Field use');

        $lrt4 = new LeaseRentTypes();
        $lrt4->setName('场地服务费');
        $lrt4->setNameEn('Field service');

        $lrt5 = new LeaseRentTypes();
        $lrt5->setName('物业管理费');
        $lrt5->setNameEn('Property management');

        $lrt6 = new LeaseRentTypes();
        $lrt6->setName('空调使用费');
        $lrt6->setNameEn('Air conditioning');

        $lrt7 = new LeaseRentTypes();
        $lrt7->setName('增值税税金');
        $lrt7->setNameEn('The VAT tax');

        $lrt8 = new LeaseRentTypes();
        $lrt8->setName('网络通讯费');
        $lrt8->setNameEn('Network');

        $lrt9 = new LeaseRentTypes();
        $lrt9->setName('其他');
        $lrt9->setNameEn('Other');

        $manager->persist($lrt1);
        $manager->persist($lrt2);
        $manager->persist($lrt3);
        $manager->persist($lrt4);
        $manager->persist($lrt5);
        $manager->persist($lrt6);
        $manager->persist($lrt7);
        $manager->persist($lrt8);
        $manager->persist($lrt9);

        $manager->flush();
    }

    public function getOrder()
    {
        return 20;
    }
}