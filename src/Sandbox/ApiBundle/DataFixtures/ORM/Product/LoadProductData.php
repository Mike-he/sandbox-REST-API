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
        $now = new \DateTime('now');
        $startDate = $now->setTime(00, 00, 00);

        $pro1 = new Product();
        $pro1->setRoom($this->getReference('room-meeting-1'));
        $pro1->setDescription('空间1');
        $pro1->setBasePrice(10);
        $pro1->setUnitPrice('hour');
        $pro1->setStartDate($startDate);
        $this->addReference('product-1', $pro1);

        $pro2 = new Product();
        $pro2->setRoom($this->getReference('room-meeting-2'));
        $pro2->setDescription('空间2');
        $pro2->setBasePrice(30);
        $pro2->setUnitPrice('hour');
        $pro2->setStartDate($startDate);
        $this->addReference('product-2', $pro2);

        $pro3 = new Product();
        $pro3->setRoom($this->getReference('room-office-1'));
        $pro3->setDescription('空间3');
        $pro3->setBasePrice(1000);
        $pro3->setUnitPrice('month');
        $pro3->setStartDate($startDate);
        $this->addReference('product-3', $pro3);

        $pro4 = new Product();
        $pro4->setRoom($this->getReference('room-office-1'));
        $pro4->setDescription('空间4');
        $pro4->setBasePrice(100);
        $pro4->setUnitPrice('day');
        $pro4->setStartDate($startDate);
        $pro4->setVisible(false);
        $this->addReference('product-4', $pro4);

        $manager->persist($pro1);
        $manager->persist($pro2);
        $manager->persist($pro3);
        $manager->persist($pro4);

        $manager->flush();
    }

    public function getOrder()
    {
        return 8;
    }
}
