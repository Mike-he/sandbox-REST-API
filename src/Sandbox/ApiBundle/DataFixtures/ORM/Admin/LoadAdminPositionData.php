<?php

namespace Sandbox\ApiBundle\DataFixtures\ORM\Admin;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sandbox\ApiBundle\Entity\Admin\AdminPosition;
use Sandbox\ApiBundle\Entity\Admin\AdminPositionIcons;

class LoadAdminPositionData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $icon1 = new AdminPositionIcons();
        $icon1->setIcon('/icon/admin_position_icon_01.png');
        $icon1->setSelectedIcon('/icon/admin_position_icon_01.png');
        $this->addReference('admin-position-icon-1', $icon1);

        $icon2 = new AdminPositionIcons();
        $icon2->setIcon('/icon/admin_position_icon_02.png');
        $icon2->setSelectedIcon('/icon/admin_position_icon_02.png');
        $this->addReference('admin-position-icon-2', $icon2);

        $p1 = new AdminPosition();
        $p1->setName('官方超级管理员');
        $p1->setParentPositionId(null);
        $p1->setPlatform('official');
        $p1->setSalesCompany($this->getReference('sales-company-sandbox'));
        $p1->setIsHidden(false);
        $p1->setIsSuperAdmin(true);
        $p1->setIcon($this->getReference('admin-position-icon-1'));
        $p1->setSortTime('1476153473566');
        $this->addReference('admin-position-official-super', $p1);

        $p2 = new AdminPosition();
        $p2->setName('销售方超级管理员');
        $p2->setParentPositionId(null);
        $p2->setPlatform('sales');
        $p2->setSalesCompany($this->getReference('sales-company-sandbox'));
        $p2->setIsHidden(false);
        $p2->setIsSuperAdmin(true);
        $p2->setIcon($this->getReference('admin-position-icon-1'));
        $p2->setSortTime('1476153473567');
        $this->addReference('admin-position-sales-sandbox-super', $p2);

        $p3 = new AdminPosition();
        $p3->setName('店铺超级管理员');
        $p3->setParentPositionId(null);
        $p3->setPlatform('shop');
        $p3->setSalesCompany($this->getReference('sales-company-sandbox'));
        $p3->setIsHidden(false);
        $p3->setIsSuperAdmin(true);
        $p3->setIcon($this->getReference('admin-position-icon-1'));
        $p3->setSortTime('1476153473568');
        $this->addReference('admin-position-shop-sandbox-super', $p3);

        $manager->persist($icon1);
        $manager->persist($icon2);
        $manager->persist($p1);
        $manager->persist($p2);
        $manager->persist($p3);

        $manager->flush();
    }

    public function getOrder()
    {
        return 4;
    }
}
