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

        $ra3 = new RoomAttachment();
        $ra3->setRoomType('meeting');
        $ra3->setAttachmentType('office');
        $ra3->setBuilding($this->getReference('room-building-for-data-structure'));
        $ra3->setContent('http://devimage.sandbox3.cn/building/49-a448-6f2b8cf4.jpg');
        $ra3->setFilename('49-a448-6f2b8cf4.jpg');
        $ra3->setPreview('http://devimage.sandbox3.cn/building/49-a448-6f2b8cf4.jpg');
        $ra3->setSize(1);
        $ra3->setCreationDate($date);

        $ra4 = new RoomAttachment();
        $ra4->setRoomType('fixed');
        $ra4->setAttachmentType('image/jpeg');
        $ra4->setBuilding($this->getReference('room-building-for-data-structure'));
        $ra4->setContent('http://devimage.sandbox3.cn/building/49-a448-6f2b8cf4.jpg');
        $ra4->setFilename('49-a448-6f2b8cf4.jpg');
        $ra4->setPreview('http://devimage.sandbox3.cn/building/49-a448-6f2b8cf4.jpg');
        $ra4->setSize(1);
        $ra4->setCreationDate($date);

        $ra5 = new RoomAttachment();
        $ra5->setRoomType('office');
        $ra5->setAttachmentType('image/jpeg');
        $ra5->setBuilding($this->getReference('room-building-for-data-structure'));
        $ra5->setContent('http://devimage.sandbox3.cn/building/49-a448-6f2b8cf4.jpg');
        $ra5->setFilename('49-a448-6f2b8cf4.jpg');
        $ra5->setPreview('http://devimage.sandbox3.cn/building/49-a448-6f2b8cf4.jpg');
        $ra5->setSize(1);
        $ra5->setCreationDate($date);

        $ra6 = new RoomAttachment();
        $ra6->setRoomType('longterm');
        $ra6->setAttachmentType('image/jpeg');
        $ra6->setBuilding($this->getReference('room-building-for-data-structure'));
        $ra6->setContent('http://devimage.sandbox3.cn/building/49-a448-6f2b8cf4.jpg');
        $ra6->setFilename('49-a448-6f2b8cf4.jpg');
        $ra6->setPreview('http://devimage.sandbox3.cn/building/49-a448-6f2b8cf4.jpg');
        $ra6->setSize(1);
        $ra6->setCreationDate($date);
        $this->addReference('room-attachment-longterm', $ra6);

        $manager->persist($ra1);
        $manager->persist($ra2);
        $manager->persist($ra3);
        $manager->persist($ra4);
        $manager->persist($ra5);
        $manager->persist($ra6);

        $manager->flush();
    }

    public function getOrder()
    {
        return 15;
    }
}
