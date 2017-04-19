<?php

namespace Sandbox\ApiBundle\DataFixtures\ORM\MembershipCard;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sandbox\ApiBundle\Entity\MembershipCard\MembershipCard;
use Sandbox\ApiBundle\Entity\MembershipCard\MembershipCardSpecification;
use Sandbox\ApiBundle\Entity\MembershipCard\MembershipOrder;
use Sandbox\ApiBundle\Entity\User\UserGroupDoors;

class LoadMembershipCardData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $card = new MembershipCard();
        $card->setAccessNo('121324');
        $card->setName('会员卡');
        $card->setBackground('http://baidu.com');
        $card->setDescription('sandbox membership card description');
        $card->setInstructions('sandbox membership card instructions');
        $card->setPhone('021-234141325');
        $card->setCompanyId($this->getReference('sales-company-sandbox')->getId());
        $card->setVisible(1);
        $manager->persist($card);

        $specification = new MembershipCardSpecification();
        $specification->setCard($card);
        $specification->setSpecification('月卡');
        $specification->setPrice('100.00');
        $specification->setValidPeriod(1);
        $specification->setUnitPrice('month');
        $manager->persist($specification);

        $specification2 = new MembershipCardSpecification();
        $specification2->setCard($card);
        $specification2->setSpecification('季卡');
        $specification2->setPrice('300.00');
        $specification2->setValidPeriod(3);
        $specification2->setUnitPrice('month');
        $manager->persist($specification2);

        $specification3 = new MembershipCardSpecification();
        $specification3->setCard($card);
        $specification3->setSpecification('年卡');
        $specification3->setPrice('1000.00');
        $specification3->setValidPeriod(12);
        $specification3->setUnitPrice('month');
        $manager->persist($specification3);

        $cardOrder = new MembershipOrder();
        $cardOrder->setUnitPrice('month');
        $cardOrder->setPrice('100.00');
        $cardOrder->setCard($card);
        $cardOrder->setOrderNumber('314819741935');
        $cardOrder->setUser($this->getReference('user-mike')->getId());
        $cardOrder->setStartDate(new \DateTime('now'));
        $cardOrder->setEndDate(new \DateTime('now'));
        $cardOrder->setValidPeriod(1);
        $cardOrder->setPayChannel('account');
        $cardOrder->setPaymentDate(new \DateTime('now'));
        $cardOrder->setInvoiced(0);
        $cardOrder->setSalesInvoice(0);
        $cardOrder->setServiceFee('0.00');
        $cardOrder->setSpecification('月卡');
        $manager->persist($cardOrder);

        $doors = new UserGroupDoors();
        $doors->setCard($card);
        $doors->setBuilding($this->getReference('room-building-for-data-structure')->getId());
        $doors->setDoorControlId(1);
        $doors->setName('大门');
        $manager->persist($doors);

        $manager->flush();
    }

    public function getOrder()
    {
        return 23;
    }
}
