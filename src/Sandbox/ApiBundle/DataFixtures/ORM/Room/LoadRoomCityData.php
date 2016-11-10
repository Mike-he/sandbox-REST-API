<?php

namespace Sandbox\ApiBundle\DataFixtures\ORM\Room;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sandbox\ApiBundle\Entity\Room\RoomCity;

class LoadRoomCityData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $rc1 = new RoomCity();
        $rc1->setKey('sh');
        $rc1->setName('上海(Shanghai)');
        $this->addReference('shanghai', $rc1);

        $rc2 = new RoomCity();
        $rc2->setKey('bj');
        $rc2->setName('北京(Beijing)');
        $this->addReference('beijing', $rc2);

        $rc3 = new RoomCity();
        $rc3->setKey('gz');
        $rc3->setName('广州(Guangzhou)');
        $this->addReference('guangzhou', $rc3);

        $rc4 = new RoomCity();
        $rc4->setKey('dl');
        $rc4->setName('大连(Dalian)');
        $this->addReference('dalian', $rc4);

        $rc5 = new RoomCity();
        $rc5->setKey('jx');
        $rc5->setName('嘉兴(Jiaxing)');
        $this->addReference('jiaxing', $rc5);

        $rc6 = new RoomCity();
        $rc6->setKey('nj');
        $rc6->setName('南京(Nanjing)');
        $this->addReference('nanjing', $rc6);

        $manager->persist($rc1);
        $manager->persist($rc2);
        $manager->persist($rc3);
        $manager->persist($rc4);
        $manager->persist($rc5);
        $manager->persist($rc6);

        $manager->flush();
    }

    public function getOrder()
    {
        return 2;
    }
}
