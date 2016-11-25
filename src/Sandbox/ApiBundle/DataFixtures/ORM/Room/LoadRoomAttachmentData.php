<?php

namespace Sandbox\ApiBundle\DataFixtures\ORM\Room;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sandbox\ApiBundle\Entity\Room\RoomAttachment;

class LoadRoomAttachmentData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $date = new \DateTime('now');

        $ra1 = new RoomAttachment();
        $ra1->setBuilding($this->getReference('room-building-for-data-structure'));
        $ra1->setFilename('room-attachment-1');
        $ra1->setAttachmentType('image/jpeg');
        $ra1->setPreview('https://testimage.sandbox3.cn/building/attachment_office_3.jpg');
        $ra1->setContent('https://image.sandbox3.cn/building/attachment_office_3.jpg');
        $ra1->setRoomType('meeting');
        $ra1->setSize(1024);
        $ra1->setCreationDate($date);
        $this->addReference('room-attachment-1', $ra1);

        $ra2 = new RoomAttachment();
        $ra2->setBuilding($this->getReference('room-building-for-data-structure'));
        $ra2->setFilename('room-attachment-2');
        $ra2->setAttachmentType('image/jpeg');
        $ra2->setPreview('https://testimage.sandbox3.cn/building/attachment_office_2.jpg');
        $ra2->setContent('https://image.sandbox3.cn/building/attachment_office_2.jpg');
        $ra2->setRoomType('fixed');
        $ra2->setSize(1025);
        $ra2->setCreationDate($date);
        $this->addReference('room-attachment-2', $ra2);

        $manager->persist($ra1);
        $manager->persist($ra2);

        $manager->flush();
    }

    public function getOrder()
    {
        return 15;
    }
}
