<?php

namespace Sandbox\ApiBundle\DataFixtures\ORM\Product;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sandbox\ApiBundle\Entity\Product\Product;

class LoadProductData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $pro1 = new Product();
        $pro1->setRoom($this->getReference('room-meeting-1'));
        $pro1->setDescription('空间1');
        $pro1->setBasePrice(10);
        $pro1->setUnitPrice('hour');
        $this->addReference('product-1', $pro1);

        $pro2 = new Product();
        $pro2->setRoom($this->getReference('room-meeting-2'));
        $pro2->setDescription('空间2');
        $pro2->setBasePrice(30);
        $pro2->setUnitPrice('hour');
        $this->addReference('product-2', $pro1);

        $pro3 = new Product();
        $pro3->setRoom($this->getReference('room-office-1'));
        $pro3->setDescription('空间3');
        $pro3->setBasePrice(1000);
        $pro3->setUnitPrice('month');
        $this->addReference('product-3', $pro1);

        $manager->persist($pro1);
        $manager->persist($pro2);
        $manager->persist($pro3);

        $manager->flush();
    }

    public function getOrder()
    {
        return 8;
    }
}
