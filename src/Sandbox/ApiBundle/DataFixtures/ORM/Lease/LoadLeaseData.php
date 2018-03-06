<?php

namespace Sandbox\ApiBundle\DataFixtures\ORM\Lease;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sandbox\ApiBundle\Entity\Lease\Lease;
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;

class LoadLeaseData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $userCustomer = $this->getReference('user-customer-1');
        $company = $this->getReference('sales-company-sandbox');

        $l1 = new Lease();
        $l1->setDeposit(12456);
        $l1->setStartDate(new \DateTime('2016-11-01'));
        $l1->setEndDate(new \DateTime('2017-07-01'));
        $l1->setLessorEmail('dong@sandbox3.cn');
        $l1->setLessorAddress('东方明珠');
        $l1->setLessorName('dong');
        $l1->setLessorPhone('13700000001');
        $l1->setLessorContact('kjlkjoie');
        $l1->setOtherExpenses('赶紧给钱');
        $l1->setMonthlyRent(8000);
        $l1->setPurpose('我也不知道');
        $l1->setSerialNumber('HT01092091029012');
        $l1->setStatus(Lease::LEASE_STATUS_PERFORMING);
        $l1->setSupplementaryTerms('woquniqutaqu');
        $l1->setTotalRent(96000);
        $l1->setProduct($this->getReference('product-for-longterm'));
        $l1->setLesseeType('personal');
        $l1->setCompanyId($company->getId());
        $l1->setLesseeCustomer($userCustomer->getId());
        $this->addReference('lease_one', $l1);

        $l2 = new Lease();
        $l2->setSerialNumber('HT345689892');
        $l2->setStartDate(new \DateTime('2016-01-01'));
        $l2->setEndDate(new \DateTime('2016-06-01'));
        $l2->setProduct($this->getReference('product-for-longterm'));
        $l2->setLesseeType('personal');
        $l2->setCompanyId($company->getId());
        $l2->setLesseeCustomer($userCustomer->getId());
        $l2->setStatus(Lease::LEASE_STATUS_DRAFTING);

        $l3 = new Lease();
        $l3->setSerialNumber('HT12356890564');
        $l3->setStartDate(new \DateTime('2016-11-01'));
        $l3->setEndDate(new \DateTime('2017-07-01'));
        $l3->setStatus(Lease::LEASE_STATUS_CLOSED);
        $l3->setProduct($this->getReference('product-for-longterm'));
        $l3->setLesseeType('personal');
        $l3->setLesseeCustomer($userCustomer->getId());
        $l3->setCompanyId($company->getId());

        $lb1 = new LeaseBill();
        $lb1->setSerialNumber('B1234567');
        $lb1->setName('账单1');
        $lb1->setDescription('账单描述1');
        $lb1->setAmount('199.9');
        $lb1->setStartDate(new \DateTime('2016-12-01'));
        $lb1->setEndDate(new \DateTime('2016-12-31'));
        $lb1->setType(LeaseBill::TYPE_LEASE);
        $lb1->setLease($l1);
        $this->addReference('lease_bill_for_type_lease', $lb1);

        $lb2 = new LeaseBill();
        $lb2->setSerialNumber('B2345678');
        $lb2->setName('其他账单1');
        $lb2->setDescription('其他账单描述1');
        $lb2->setAmount('88.8');
        $lb2->setStartDate(new \DateTime('2016-11-11'));
        $lb2->setEndDate(new \DateTime('2016-12-12'));
        $lb2->setType(LeaseBill::TYPE_OTHER);
        $lb2->setStatus(LeaseBill::STATUS_UNPAID);
        $lb2->setLease($l1);
        $lb2->setRevisedAmount('88.8');
        $this->addReference('lease_bill_for_type_other', $lb2);

        $manager->persist($l1);
        $manager->persist($l2);
        $manager->persist($l3);

        $manager->persist($lb1);
        $manager->persist($lb2);

        $manager->flush();
    }

    public function getOrder()
    {
        return 21;
    }
}
