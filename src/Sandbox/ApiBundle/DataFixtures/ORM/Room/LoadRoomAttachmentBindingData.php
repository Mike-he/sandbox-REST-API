<?php

namespace Sandbox\ApiBundle\DataFixtures\ORM\Room;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sandbox\ApiBundle\Entity\Room\RoomAttachmentBinding;

class LoadRoomAttachmentBindingData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $rab1 = new RoomAttachmentBinding();
        $rab1->setRoom($this->getReference('room-for-get-spaces-data-structure'));
        $rab1->setAttachmentId($this->getReference('room-attachment-1'));

        $rab2 = new RoomAttachmentBinding();
        $rab2->setRoom($this->getReference('fixed-room-for-get-spaces-data-structure'));
        $rab2->setAttachmentId($this->getReference('room-attachment-2'));

        $rab3 = new RoomAttachmentBinding();
        $rab3->setRoom($this->getReference('longterm-room-1'));
        $rab3->setAttachmentId($this->getReference('room-attachment-longterm'));

        $manager->persist($rab1);
        $manager->persist($rab2);
        $manager->persist($rab3);

        $manager->flush();
    }

    public function getOrder()
    {
        return 16;
    }
}
