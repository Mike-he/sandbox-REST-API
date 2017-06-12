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
        $this->addReference('room-for-get-spaces-data-structure', $room1);

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

        $room4 = new Room();
        $room4->setName('office-room2');
        $room4->setDescription('独享办公室2');
        $room4->setCity($this->getReference('shanghai'));
        $room4->setBuilding($this->getReference('room-building-for-data-structure'));
        $room4->setFloor($this->getReference('room-floor-1'));
        $room4->setNumber(100003);
        $room4->setArea(10);
        $room4->setType('office');
        $room4->setAllowedPeople(10);
        $this->addReference('room-office-2', $room4);

        $room5 = new Room();
        $room5->setName('office-room3');
        $room5->setDescription('独享办公室3');
        $room5->setCity($this->getReference('shanghai'));
        $room5->setBuilding($this->getReference('room-building-for-data-structure'));
        $room5->setFloor($this->getReference('room-floor-1'));
        $room5->setNumber(100003);
        $room5->setArea(10);
        $room5->setType('office');
        $room5->setAllowedPeople(10);
        $this->addReference('room-office-3', $room5);

        $room6 = new Room();
        $room6->setName('fixed-room1');
        $room6->setDescription('固定工位办公室1');
        $room6->setCity($this->getReference('shanghai'));
        $room6->setBuilding($this->getReference('room-building-for-data-structure'));
        $room6->setFloor($this->getReference('room-floor-1'));
        $room6->setNumber(100006);
        $room6->setArea(200);
        $room6->setType('desk');
        $room6->setAllowedPeople(20);
        $this->addReference('fixed-room-for-get-spaces-data-structure', $room6);

        $room7 = new Room();
        $room7->setName('longterm-room1');
        $room7->setDescription('长租办公室1');
        $room7->setCity($this->getReference('shanghai'));
        $room7->setBuilding($this->getReference('room-building-for-data-structure'));
        $room7->setFloor($this->getReference('room-floor-1'));
        $room7->setArea(200);
        $room7->setType('office');
        $room7->setAllowedPeople(30);
        $this->addReference('longterm-room-1', $room7);

        $manager->persist($room2);
        $manager->persist($room3);
        $manager->persist($room4);
        $manager->persist($room5);
        $manager->persist($room6);
        $manager->persist($room7);

        // keep in last one
        $manager->persist($room1);

        $manager->flush();
    }

    public function getOrder()
    {
        return 9;
    }
}
