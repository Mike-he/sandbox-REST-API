<?php

namespace Sandbox\ApiBundle\DataFixtures\ORM\Lease;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sandbox\ApiBundle\Entity\Lease\Lease;

class LoadLeaseData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $date = new \DateTime('now');

        $l1 = new Lease();
        $l1->setDeposit(12456);
        $l1->setSupervisor($this->getReference('user-mike'));
        $l1->setDrawee($this->getReference('user-mike'));
        $l1->setStartDate($date);
        $l1->setEndDate($date);
        $l1->setLesseeEmail('mike@sandbox3.cn');
        $l1->setLesseeAddress('展想广场');
        $l1->setLesseeName('mike');
        $l1->setLesseePhone('13100000001');
        $l1->setLesseeContact('xlkjli');
        $l1->setLessorEmail('dong@sandbox3.cn');
        $l1->setLessorAddress('东方明珠');
        $l1->setLessorName('dong');
        $l1->setLessorPhone('13700000001');
        $l1->setLessorContact('kjlkjoie');
        $l1->setOtherExpenses('赶紧给钱');
        $l1->setMonthlyRent(8000);
        $l1->setPurpose('我也不知道');
        $l1->setSerialNumber('HT01092091029012');
        $l1->setStatus('reviewing');
        $l1->setSupplementaryTerms('woquniqutaqu');
        $l1->setTotalRent(96000);

        $l2 = new Lease();
        $l2->setSupervisor($this->getReference('user-mike'));
        $l2->setDrawee($this->getReference('user-mike'));

        $l2 = new Lease();
        $l2->setSupervisor($this->getReference('user-mike'));
        $l2->setDrawee($this->getReference('user-mike'));

        $l3 = new Lease();
        $l3->setSupervisor($this->getReference('user-mike'));
        $l3->setDrawee($this->getReference('user-mike'));

        $manager->persist($l1);
        $manager->persist($l2);
        $manager->persist($l3);

        $manager->flush();
    }

    public function getOrder()
    {
        return 17;
    }
}
