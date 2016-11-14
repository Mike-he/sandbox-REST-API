<?php

namespace Sandbox\ApiBundle\DataFixtures\ORM\Room;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sandbox\ApiBundle\Entity\Room\Room;
use Sandbox\ApiBundle\Entity\Room\RoomFloor;

class LoadRoomData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $rf1 = new RoomFloor();
        $rf1->setFloorNumber(1);
        $rf1->setBuilding($this->getReference('room-building-for-pengding-1'));
        $this->addReference('room-floor-1', $rf1);

        $manager->persist($rf1);

        $room1 = new Room();
        $room1->setName('meeting-room1');
        $room1->setDescription('会议室1');
        $room1->setCity($this->getReference('shanghai'));
        $room1->setBuilding($this->getReference('room-building-for-data-structure'));
        $room1->setFloor($this->getReference('room-floor-1'));
        $room1->setNumber(100001);
        $room1->setArea(20);
        $room1->setType('meeting');
        $room1->setAllowedPeople(10);
        $this->addReference('room-meeting-1', $room1);

        $room2 = new Room();
        $room2->setName('meeting-room2');
        $room2->setDescription('会议室2');
        $room2->setCity($this->getReference('shanghai'));
        $room2->setBuilding($this->getReference('room-building-for-data-structure'));
        $room2->setFloor($this->getReference('room-floor-1'));
        $room2->setNumber(100002);
        $room2->setArea(20);
        $room2->setType('meeting');
        $room2->setAllowedPeople(10);
        $this->addReference('room-meeting-2', $room2);

        $room3 = new Room();
        $room3->setName('office-room1');
        $room3->setDescription('独享办公室1');
        $room3->setCity($this->getReference('shanghai'));
        $room3->setBuilding($this->getReference('room-building-for-data-structure'));
        $room3->setFloor($this->getReference('room-floor-1'));
        $room3->setNumber(100003);
        $room3->setArea(10);
        $room3->setType('office');
        $room3->setAllowedPeople(10);
        $this->addReference('room-office-1', $room3);

        $manager->persist($room1);
        $manager->persist($room2);
        $manager->persist($room3);

        $manager->flush();
    }

    public function getOrder()
    {
        return 7;
    }
}
