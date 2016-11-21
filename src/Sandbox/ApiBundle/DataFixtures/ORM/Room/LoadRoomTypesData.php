<?php

namespace Sandbox\ApiBundle\DataFixtures\ORM\Room;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sandbox\ApiBundle\Entity\Room\RoomTypes;
use Sandbox\ApiBundle\Entity\Room\RoomTypeUnit;

class LoadRoomTypesData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $rt1 = new RoomTypes();
        $rt1->setName('office');
        $rt1->setDescription('Private Office');
        $rt1->setIcon('/icon/1.png');
        $rt1->setType('seconds');
        $this->addReference('first-room-type', $rt1);

        $rt2 = new RoomTypes();
        $rt2->setName('meeting');
        $rt2->setDescription('Meeting Room');
        $rt2->setIcon('/icon/2.png');
        $rt2->setType('seconds');
        $this->addReference('second-room-type', $rt2);

        $rt3 = new RoomTypes();
        $rt3->setName('fixed');
        $rt3->setDescription('Fixed Room');
        $rt3->setIcon('/icon/3.png');
        $rt3->setType('seconds');
        $this->addReference('third-room-type', $rt3);

        $rtu1 = new RoomTypeUnit();
        $rt1->setUnits([$rtu1]);
        $rtu1->setType($rt1);
        $rtu1->setDescription('Rent by the hour');
        $rtu1->setUnit('hour');

        $rtu2 = new RoomTypeUnit();
        $rt2->setUnits([$rtu2]);
        $rtu2->setType($rt2);
        $rtu2->setDescription('Rent by the month');
        $rtu2->setUnit('month');

        $rtu3 = new RoomTypeUnit();
        $rt3->setUnits([$rtu3]);
        $rtu3->setType($rt3);
        $rtu3->setDescription('Rent by the hour');
        $rtu3->setUnit('hour');

        $manager->persist($rt1);
        $manager->persist($rt2);
        $manager->persist($rt3);
        $manager->persist($rtu1);
        $manager->persist($rtu2);
        $manager->persist($rtu3);

        $manager->flush();
    }

    public function getOrder()
    {
        return 8;
    }
}
