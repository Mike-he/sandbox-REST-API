<?php

namespace Sandbox\ApiBundle\DataFixtures\ORM\Admin;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sandbox\ApiBundle\Entity\Admin\AdminPositionGroupBinding;

class LoadAdminPositionGroupsBindingData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $binding1 = new AdminPositionGroupBinding();
        $binding1->setPosition($this->getReference('admin-position-official-super'));
        $binding1->setGroup($this->getReference('official-group-dashboard'));

        $binding2 = new AdminPositionGroupBinding();
        $binding2->setPosition($this->getReference('admin-position-sales-sandbox-super'));
        $binding2->setGroup($this->getReference('official-group-banner'));

        $binding3 = new AdminPositionGroupBinding();
        $binding3->setPosition($this->getReference('admin-position-shop-sandbox-super'));
        $binding3->setGroup($this->getReference('official-group-news'));

        $manager->persist($binding1);
        $manager->persist($binding2);
        $manager->persist($binding3);

        $manager->flush();
    }

    public function getOrder()
    {
        return 18;
    }
}
