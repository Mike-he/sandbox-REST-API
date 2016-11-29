<?php

namespace Sandbox\ApiBundle\DataFixtures\ORM\Room;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sandbox\ApiBundle\Entity\Room\RoomFixed;

class LoadRoomFixedData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $rf1 = new RoomFixed();
        $rf1->setBasePrice(12.5);
        $rf1->setRoom($this->getReference('fixed-room-for-get-spaces-data-structure'));
        $rf1->setSeatNumber(100001);
        $this->addReference('room-seat-1', $rf1);

        $rf2 = new RoomFixed();
        $rf2->setBasePrice(15);
        $rf2->setRoom($this->getReference('fixed-room-for-get-spaces-data-structure'));
        $rf2->setSeatNumber(100002);
        $this->addReference('room-seat-2', $rf2);

        $manager->persist($rf1);
        $manager->persist($rf2);

        $manager->flush();
    }

    public function getOrder()
    {
        return 10;
    }
}
