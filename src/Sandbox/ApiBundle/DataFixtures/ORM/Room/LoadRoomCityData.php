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
        $rc0 = new RoomCity();
        $rc0->setName('中国');
        $rc0->setLevel(1);
        $this->addReference('china', $rc0);

        $china = $this->getReference('china');

        $rc1 = new RoomCity();
        $rc1->setName('上海');
        $rc1->setParent($china);
        $rc1->setLevel(2);
        $this->addReference('shanghai_province', $rc1);

        $rc2 = new RoomCity();
        $rc2->setName('上海市');
        $rc2->setParent($rc1);
        $rc2->setLevel(3);
        $this->addReference('shanghai', $rc2);

        $rc3 = new RoomCity();
        $rc3->setName('黄浦区');
        $rc3->setParent($rc2);
        $rc3->setLevel(4);
        $this->addReference('huangpuqu', $rc3);

        $rc4 = new RoomCity();
        $rc4->setName('北京');
        $rc4->setLevel(2);
        $rc4->setParent($china);
        $this->addReference('beijing_province', $rc4);

        $rc5 = new RoomCity();
        $rc5->setName('北京市');
        $rc5->setParent($rc4);
        $rc5->setLevel(3);
        $this->addReference('beijing', $rc5);

        $rc6 = new RoomCity();
        $rc6->setName('东城区');
        $rc6->setParent($rc5);
        $rc6->setLevel(4);
        $this->addReference('dongchengqu', $rc6);

        $rc7 = new RoomCity();
        $rc7->setName('浦东新区');
        $rc7->setParent($rc2);
        $rc7->setLevel(4);
        $this->addReference('pudongxinqu', $rc7);

        $manager->persist($rc0);
        $manager->persist($rc1);
        $manager->persist($rc2);
        $manager->persist($rc3);
        $manager->persist($rc4);
        $manager->persist($rc5);
        $manager->persist($rc6);
        $manager->persist($rc7);

        $manager->flush();
    }

    public function getOrder()
    {
        return 2;
    }
}
