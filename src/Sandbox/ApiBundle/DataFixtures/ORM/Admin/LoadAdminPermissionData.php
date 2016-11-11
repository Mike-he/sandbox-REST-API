<?php

namespace Sandbox\ApiBundle\DataFixtures\ORM\Admin;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;

class LoadAdminPermissionData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $p1 = new AdminPermission();
        $p1->setKey('platform.order');
        $p1->setName('订单管理');
        $p1->setPlatform('official');
        $p1->setLevel('global');
        $p1->setMaxOpLevel(2);
        $p1->setOpLevelSelect('1,2');
        $this->addReference('platform-order', $p1);

        $p2 = new AdminPermission();
        $p2->setKey('platform.user');
        $p2->setName('用户管理');
        $p2->setPlatform('official');
        $p2->setLevel('global');
        $p2->setMaxOpLevel(3);
        $p2->setOpLevelSelect('1,2,3');
        $this->addReference('platform-user', $p2);

        $p3 = new AdminPermission();
        $p3->setKey('platform.admin');
        $p3->setName('管理员管理');
        $p3->setPlatform('official');
        $p3->setLevel('global');
        $p3->setMaxOpLevel(2);
        $p3->setOpLevelSelect('1,2');
        $this->addReference('platform-admin', $p3);

        $p4 = new AdminPermission();
        $p4->setKey('platform.announcement');
        $p4->setName('通知管理');
        $p4->setPlatform('official');
        $p4->setLevel('global');
        $p4->setMaxOpLevel(2);
        $p4->setOpLevelSelect('1,2');
        $this->addReference('platform-announcement', $p4);

        $p5 = new AdminPermission();
        $p5->setKey('platform.dashboard');
        $p5->setName('控制台管理');
        $p5->setPlatform('official');
        $p5->setLevel('global');
        $p5->setMaxOpLevel(1);
        $p5->setOpLevelSelect('1');
        $this->addReference('platform-dashboard', $p5);

        $p6 = new AdminPermission();
        $p6->setKey('platform.event');
        $p6->setName('活动管理');
        $p6->setPlatform('official');
        $p6->setLevel('global');
        $p6->setMaxOpLevel(2);
        $p6->setOpLevelSelect('1,2');
        $this->addReference('platform-event', $p6);

        $p7 = new AdminPermission();
        $p7->setKey('platform.banner');
        $p7->setName('横幅管理');
        $p7->setPlatform('official');
        $p7->setLevel('global');
        $p7->setMaxOpLevel(2);
        $p7->setOpLevelSelect('1,2');
        $this->addReference('platform-banner', $p7);

        $p8 = new AdminPermission();
        $p8->setKey('platform.news');
        $p8->setName('新闻管理');
        $p8->setPlatform('official');
        $p8->setLevel('global');
        $p8->setMaxOpLevel(2);
        $p8->setOpLevelSelect('1,2');
        $this->addReference('platform-news', $p8);

        $p9 = new AdminPermission();
        $p9->setKey('platform.message');
        $p9->setName('消息管理');
        $p9->setPlatform('official');
        $p9->setLevel('global');
        $p9->setMaxOpLevel(2);
        $p9->setOpLevelSelect('1,2');
        $this->addReference('platform-message', $p9);

        $p10 = new AdminPermission();
        $p10->setKey('platform.verify');
        $p10->setName('审查管理');
        $p10->setPlatform('official');
        $p10->setLevel('global');
        $p10->setMaxOpLevel(2);
        $p10->setOpLevelSelect('1,2');
        $this->addReference('platform-verify', $p10);

        $p11 = new AdminPermission();
        $p11->setKey('platform.sales');
        $p11->setName('销售方管理');
        $p11->setPlatform('official');
        $p11->setLevel('global');
        $p11->setMaxOpLevel(3);
        $p11->setOpLevelSelect('1,2,3');

        $p12 = new AdminPermission();
        $p12->setKey('platform.invoice');
        $p12->setName('发票管理');
        $p12->setPlatform('official');
        $p12->setLevel('global');
        $p12->setMaxOpLevel(2);
        $p12->setOpLevelSelect('1,2');

        $p13 = new AdminPermission();
        $p13->setKey('platform.access');
        $p13->setName('门禁系统');
        $p13->setPlatform('official');
        $p13->setLevel('global');
        $p13->setMaxOpLevel(2);
        $p13->setOpLevelSelect('1,2');

        $p14 = new AdminPermission();
        $p14->setKey('platform.room');
        $p14->setName('空间管理');
        $p14->setPlatform('official');
        $p14->setLevel('global');
        $p14->setMaxOpLevel(2);
        $p14->setOpLevelSelect('1,2');

        $p15 = new AdminPermission();
        $p15->setKey('platform.product');
        $p15->setName('商品管理');
        $p15->setPlatform('official');
        $p15->setLevel('global');
        $p15->setMaxOpLevel(2);
        $p15->setOpLevelSelect('1,2');

        $p16 = new AdminPermission();
        $p16->setKey('platform.price');
        $p16->setName('价格体系管理');
        $p16->setPlatform('official');
        $p16->setLevel('global');
        $p16->setMaxOpLevel(2);
        $p16->setOpLevelSelect('1,2');

        $p17 = new AdminPermission();
        $p17->setKey('platform.building');
        $p17->setName('大楼管理');
        $p17->setPlatform('official');
        $p17->setLevel('global');
        $p17->setMaxOpLevel(2);
        $p17->setOpLevelSelect('1,2');

        $p18 = new AdminPermission();
        $p18->setKey('platform.bulletin');
        $p18->setName('说明发布');
        $p18->setPlatform('official');
        $p18->setLevel('global');
        $p18->setMaxOpLevel(2);
        $p18->setOpLevelSelect('1,2');

        $p19 = new AdminPermission();
        $p19->setKey('platform.order.reserve');
        $p19->setName('订单预留');
        $p19->setPlatform('official');
        $p19->setLevel('global');
        $p19->setMaxOpLevel(2);
        $p19->setOpLevelSelect('1,2');

        $p20 = new AdminPermission();
        $p20->setKey('platform.order.preorder');
        $p20->setName('订单预定');
        $p20->setPlatform('official');
        $p20->setLevel('global');
        $p20->setMaxOpLevel(2);
        $p20->setOpLevelSelect('1,2');

        $p21 = new AdminPermission();
        $p21->setKey('platform.product.appointment');
        $p21->setName('预约审核');
        $p21->setPlatform('official');
        $p21->setLevel('global');
        $p21->setMaxOpLevel(2);
        $p21->setOpLevelSelect('1,2');

        $p22 = new AdminPermission();
        $p22->setKey('platform.log');
        $p22->setName('日志管理');
        $p22->setPlatform('official');
        $p22->setLevel('global');
        $p22->setMaxOpLevel(2);
        $p22->setOpLevelSelect('1,2');

        $p23 = new AdminPermission();
        $p23->setKey('platform.advertising');
        $p23->setName('广告管理');
        $p23->setPlatform('official');
        $p23->setLevel('global');
        $p23->setMaxOpLevel(2);
        $p23->setOpLevelSelect('1,2');

        $p24 = new AdminPermission();
        $p24->setKey('sales.platform.dashboard');
        $p24->setName('控制台管理');
        $p24->setPlatform('sales');
        $p24->setLevel('global');
        $p24->setMaxOpLevel(1);
        $p24->setOpLevelSelect('1');

        $p25 = new AdminPermission();
        $p25->setKey('sales.platform.admin');
        $p25->setName('管理员管理');
        $p25->setPlatform('sales');
        $p25->setLevel('global');
        $p25->setMaxOpLevel(2);
        $p25->setOpLevelSelect('1,2');

        $p26 = new AdminPermission();
        $p26->setKey('sales.platform.building');
        $p26->setName('项目新增');
        $p26->setPlatform('sales');
        $p26->setLevel('global');
        $p26->setMaxOpLevel(2);
        $p26->setOpLevelSelect('2');

        $p27 = new AdminPermission();
        $p27->setKey('sales.platform.invoice');
        $p27->setName('发票管理');
        $p27->setPlatform('sales');
        $p27->setLevel('global');
        $p27->setMaxOpLevel(2);
        $p27->setOpLevelSelect('1,2');

        $p28 = new AdminPermission();
        $p28->setKey('sales.platform.event');
        $p28->setName('活动管理');
        $p28->setPlatform('sales');
        $p28->setLevel('global');
        $p28->setMaxOpLevel(2);
        $p28->setOpLevelSelect('1,2');

        $p29 = new AdminPermission();
        $p29->setKey('sales.building.price');
        $p29->setName('价格模板管理');
        $p29->setPlatform('sales');
        $p29->setLevel('specify');
        $p29->setMaxOpLevel(2);
        $p29->setOpLevelSelect('1,2');

        $p30 = new AdminPermission();
        $p30->setKey('sales.building.order');
        $p30->setName('订单管理');
        $p30->setPlatform('sales');
        $p30->setLevel('specify');
        $p30->setMaxOpLevel(2);
        $p30->setOpLevelSelect('1,2');

        $p31 = new AdminPermission();
        $p31->setKey('sales.building.order.reserve');
        $p31->setName('订单预留');
        $p31->setPlatform('sales');
        $p31->setLevel('specify');
        $p31->setMaxOpLevel(2);
        $p31->setOpLevelSelect('1,2');

        $p32 = new AdminPermission();
        $p32->setKey('sales.building.order.preorder');
        $p32->setName('订单预订');
        $p32->setPlatform('sales');
        $p32->setLevel('specify');
        $p32->setMaxOpLevel(2);
        $p32->setOpLevelSelect('1,2');

        $p33 = new AdminPermission();
        $p33->setKey('sales.building.building');
        $p33->setName('项目管理');
        $p33->setPlatform('sales');
        $p33->setLevel('specify');
        $p33->setMaxOpLevel(2);
        $p33->setOpLevelSelect('1,2');

        $p34 = new AdminPermission();
        $p34->setKey('sales.building.user');
        $p34->setName('用户管理');
        $p34->setPlatform('sales');
        $p34->setLevel('specify');
        $p34->setMaxOpLevel(2);
        $p34->setOpLevelSelect('1,2');

        $p35 = new AdminPermission();
        $p35->setKey('sales.building.room');
        $p35->setName('空间管理');
        $p35->setPlatform('sales');
        $p35->setLevel('specify');
        $p35->setMaxOpLevel(2);
        $p35->setOpLevelSelect('1,2');

        $p36 = new AdminPermission();
        $p36->setKey('sales.building.product');
        $p36->setName('商品管理');
        $p36->setPlatform('sales');
        $p36->setLevel('specify');
        $p36->setMaxOpLevel(2);
        $p36->setOpLevelSelect('1,2');

        $p37 = new AdminPermission();
        $p37->setKey('sales.building.access');
        $p37->setName('门禁管理');
        $p37->setPlatform('sales');
        $p37->setLevel('specify');
        $p37->setMaxOpLevel(2);
        $p37->setOpLevelSelect('1,2');

        $p38 = new AdminPermission();
        $p38->setKey('shop.platform.dashboard');
        $p38->setName('控制台管理');
        $p38->setPlatform('shop');
        $p38->setLevel('global');
        $p38->setMaxOpLevel(1);
        $p38->setOpLevelSelect('1');

        $p39 = new AdminPermission();
        $p39->setKey('shop.platform.admin');
        $p39->setName('管理员管理');
        $p39->setPlatform('shop');
        $p39->setLevel('global');
        $p39->setMaxOpLevel(2);
        $p39->setOpLevelSelect('1,2');

        $p40 = new AdminPermission();
        $p40->setKey('shop.platform.shop');
        $p40->setName('商店新增');
        $p40->setPlatform('shop');
        $p40->setLevel('global');
        $p40->setMaxOpLevel(2);
        $p40->setOpLevelSelect('1,2');

        $p41 = new AdminPermission();
        $p41->setKey('shop.platform.spec');
        $p41->setName('规格管理');
        $p41->setPlatform('shop');
        $p41->setLevel('global');
        $p41->setMaxOpLevel(2);
        $p41->setOpLevelSelect('1,2');

        $p42 = new AdminPermission();
        $p42->setKey('shop.shop.shop');
        $p42->setName('商店管理');
        $p42->setPlatform('shop');
        $p42->setLevel('specify');
        $p42->setMaxOpLevel(2);
        $p42->setOpLevelSelect('1,2');

        $p43 = new AdminPermission();
        $p43->setKey('shop.shop.order');
        $p43->setName('订单管理');
        $p43->setPlatform('shop');
        $p43->setLevel('specify');
        $p43->setMaxOpLevel(2);
        $p43->setOpLevelSelect('1,2');

        $p44 = new AdminPermission();
        $p44->setKey('shop.shop.product');
        $p44->setName('商品管理');
        $p44->setPlatform('shop');
        $p44->setLevel('specify');
        $p44->setMaxOpLevel(2);
        $p44->setOpLevelSelect('1,2');

        $p45 = new AdminPermission();
        $p45->setKey('shop.shop.kitchen');
        $p45->setName('传菜系统管理');
        $p45->setPlatform('shop');
        $p45->setLevel('specify');
        $p45->setMaxOpLevel(2);
        $p45->setOpLevelSelect('1,2');

        $p46 = new AdminPermission();
        $p46->setKey('platform.order.refund');
        $p46->setName('退款');
        $p46->setPlatform('official');
        $p46->setLevel('global');
        $p46->setMaxOpLevel(2);
        $p46->setOpLevelSelect('2');

        $p47 = new AdminPermission();
        $p47->setKey('platform.finance');
        $p47->setName('财务管理');
        $p47->setPlatform('official');
        $p47->setLevel('global');
        $p47->setMaxOpLevel(2);
        $p47->setOpLevelSelect('1,2');

        $manager->persist($p1);
        $manager->persist($p2);
        $manager->persist($p3);
        $manager->persist($p4);
        $manager->persist($p5);
        $manager->persist($p6);
        $manager->persist($p7);
        $manager->persist($p8);
        $manager->persist($p9);
        $manager->persist($p10);
        $manager->persist($p11);
        $manager->persist($p12);
        $manager->persist($p13);
        $manager->persist($p14);
        $manager->persist($p15);
        $manager->persist($p16);
        $manager->persist($p17);
        $manager->persist($p18);
        $manager->persist($p19);
        $manager->persist($p20);
        $manager->persist($p21);
        $manager->persist($p22);
        $manager->persist($p23);
        $manager->persist($p24);
        $manager->persist($p25);
        $manager->persist($p26);
        $manager->persist($p27);
        $manager->persist($p28);
        $manager->persist($p29);
        $manager->persist($p30);
        $manager->persist($p31);
        $manager->persist($p32);
        $manager->persist($p33);
        $manager->persist($p34);
        $manager->persist($p35);
        $manager->persist($p36);
        $manager->persist($p37);
        $manager->persist($p38);
        $manager->persist($p39);
        $manager->persist($p40);
        $manager->persist($p41);
        $manager->persist($p42);
        $manager->persist($p43);
        $manager->persist($p44);
        $manager->persist($p45);
        $manager->persist($p46);
        $manager->persist($p47);

        $manager->flush();
    }

    public function getOrder()
    {
        return 5;
    }
}
