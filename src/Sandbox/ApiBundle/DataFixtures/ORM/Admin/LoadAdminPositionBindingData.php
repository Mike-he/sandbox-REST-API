<?php

namespace Sandbox\ApiBundle\DataFixtures\ORM\Admin;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sandbox\ApiBundle\Entity\Admin\AdminPositionUserBinding;

class LoadAdminPositionBindingData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $binding1 = new AdminPositionUserBinding();
        $binding1->setUser($this->getReference('user-mike'));
        $binding1->setPosition($this->getReference('admin-position-official-super'));

        $binding2 = new AdminPositionUserBinding();
        $binding2->setUser($this->getReference('user-mike'));
        $binding2->setPosition($this->getReference('admin-position-sales-sandbox-super'));

        $binding3 = new AdminPositionUserBinding();
        $binding3->setUser($this->getReference('user-mike'));
        $binding3->setPosition($this->getReference('admin-position-shop-sandbox-super'));

        $binding4 = new AdminPositionUserBinding();
        $binding4->setUser($this->getReference('user-2'));
        $binding4->setPosition($this->getReference('admin-position-official-super'));

        $binding5 = new AdminPositionUserBinding();
        $binding5->setUser($this->getReference('user-2'));
        $binding5->setPosition($this->getReference('admin-position-sales-sandbox-super'));

        $binding6 = new AdminPositionUserBinding();
        $binding6->setUser($this->getReference('user-2'));
        $binding6->setPosition($this->getReference('admin-position-shop-sandbox-super'));

        $binding7 = new AdminPositionUserBinding();
        $binding7->setUser($this->getReference('user-3'));
        $binding7->setPosition($this->getReference('admin-position-official-super'));

        $binding8 = new AdminPositionUserBinding();
        $binding8->setUser($this->getReference('user-3'));
        $binding8->setPosition($this->getReference('admin-position-sales-sandbox-super'));

        $binding9 = new AdminPositionUserBinding();
        $binding9->setUser($this->getReference('user-3'));
        $binding9->setPosition($this->getReference('admin-position-shop-sandbox-super'));

        $manager->persist($binding1);
        $manager->persist($binding2);
        $manager->persist($binding3);
        $manager->persist($binding4);
        $manager->persist($binding5);
        $manager->persist($binding6);
        $manager->persist($binding7);
        $manager->persist($binding8);
        $manager->persist($binding9);

        $manager->flush();
    }

    public function getOrder()
    {
        return 11;
    }
}
