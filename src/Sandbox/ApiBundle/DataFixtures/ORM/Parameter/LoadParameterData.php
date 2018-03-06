<?php

namespace Sandbox\ApiBundle\DataFixtures\ORM\Parameter;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sandbox\ApiBundle\Entity\Parameter\Parameter;

class LoadParameterData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $p0 = new Parameter();
        $p0->setKey('banner_top');
        $p0->setValue('5');

        $p1 = new Parameter();
        $p1->setKey('all_spaces');
        $p1->setValue('https://testmobile.sandbox3.cn/search-xiehe?');

        $p2 = new Parameter();
        $p2->setKey('quick_booking');
        $p2->setValue('https://testmobile.sandbox3.cn/search-xiehe?');

        $manager->persist($p0);
        $manager->persist($p1);
        $manager->persist($p2);

        $manager->flush();
    }

    public function getOrder()
    {
        return 13;
    }
}
