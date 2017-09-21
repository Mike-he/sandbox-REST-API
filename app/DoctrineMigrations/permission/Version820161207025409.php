<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionGroupMap;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionGroups;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version820161207025409 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
    }

    public function postUp(Schema $schema)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        // official
        $officialPermissionLease = new AdminPermission();
        $officialPermissionLease->setKey(AdminPermission::KEY_OFFICIAL_PLATFORM_LONG_TERM_LEASE);
        $officialPermissionLease->setName('合同管理');
        $officialPermissionLease->setLevel('global');
        $officialPermissionLease->setPlatform('official');
        $officialPermissionLease->setOpLevelSelect('1');
        $officialPermissionLease->setMaxOpLevel('1');

        $officialPermissionLongTermAppointment = new AdminPermission();
        $officialPermissionLongTermAppointment->setKey(AdminPermission::KEY_OFFICIAL_PLATFORM_LONG_TERM_APPOINTMENT);
        $officialPermissionLongTermAppointment->setName('长租申请');
        $officialPermissionLongTermAppointment->setLevel('global');
        $officialPermissionLongTermAppointment->setPlatform('official');
        $officialPermissionLongTermAppointment->setOpLevelSelect('1');
        $officialPermissionLongTermAppointment->setMaxOpLevel('1');

        $officialGroupDashboard = new AdminPermissionGroups();
        $officialGroupDashboard->setGroupKey('dashboard');
        $officialGroupDashboard->setGroupName('控制台');
        $officialGroupDashboard->setPlatform('official');

        $officialBanner = new AdminPermissionGroups();
        $officialBanner->setGroupKey('banner');
        $officialBanner->setGroupName('横幅');
        $officialBanner->setPlatform('official');

        $officialGroupNews = new AdminPermissionGroups();
        $officialGroupNews->setGroupKey('news');
        $officialGroupNews->setGroupName('新闻');
        $officialGroupNews->setPlatform('official');

        $officialGroupTrade = new AdminPermissionGroups();
        $officialGroupTrade->setGroupKey('trade');
        $officialGroupTrade->setGroupName('交易管理');
        $officialGroupTrade->setPlatform('official');

        $officialGroupUser = new AdminPermissionGroups();
        $officialGroupUser->setGroupKey('user');
        $officialGroupUser->setGroupName('用户管理');
        $officialGroupUser->setPlatform('official');

        $officialGroupSales = new AdminPermissionGroups();
        $officialGroupSales->setGroupKey('sales');
        $officialGroupSales->setGroupName('销售方');
        $officialGroupSales->setPlatform('official');

        $officialGroupSpace = new AdminPermissionGroups();
        $officialGroupSpace->setGroupKey('space');
        $officialGroupSpace->setGroupName('空间管理');
        $officialGroupSpace->setPlatform('official');

        $officialGroupAdmin = new AdminPermissionGroups();
        $officialGroupAdmin->setGroupKey('admin');
        $officialGroupAdmin->setGroupName('管理员');
        $officialGroupAdmin->setPlatform('official');

        $officialGroupLog = new AdminPermissionGroups();
        $officialGroupLog->setGroupKey('log');
        $officialGroupLog->setGroupName('管理员日志');
        $officialGroupLog->setPlatform('official');

        $officialGroupAnnouncement = new AdminPermissionGroups();
        $officialGroupAnnouncement->setGroupKey('announcement');
        $officialGroupAnnouncement->setGroupName('通知');
        $officialGroupAnnouncement->setPlatform('official');

        $officialGroupMessage = new AdminPermissionGroups();
        $officialGroupMessage->setGroupKey('message');
        $officialGroupMessage->setGroupName('消息');
        $officialGroupMessage->setPlatform('official');

        $officialGroupActivity = new AdminPermissionGroups();
        $officialGroupActivity->setGroupKey('activity');
        $officialGroupActivity->setGroupName('活动');
        $officialGroupActivity->setPlatform('official');

        $officialGroupVerify = new AdminPermissionGroups();
        $officialGroupVerify->setGroupKey('verify');
        $officialGroupVerify->setGroupName('审查');
        $officialGroupVerify->setPlatform('official');

        $officialGroupInvoice = new AdminPermissionGroups();
        $officialGroupInvoice->setGroupKey('invoice');
        $officialGroupInvoice->setGroupName('发票管理');
        $officialGroupInvoice->setPlatform('official');

        $officialGroupBulletin = new AdminPermissionGroups();
        $officialGroupBulletin->setGroupKey('bulletin');
        $officialGroupBulletin->setGroupName('创合说明发布');
        $officialGroupBulletin->setPlatform('official');

        $officialGroupProductAppointment = new AdminPermissionGroups();
        $officialGroupProductAppointment->setGroupKey('product_appointment');
        $officialGroupProductAppointment->setGroupName('预约审核');
        $officialGroupProductAppointment->setPlatform('official');

        $officialGroupCommercial = new AdminPermissionGroups();
        $officialGroupCommercial->setGroupKey('commercial');
        $officialGroupCommercial->setGroupName('广告');
        $officialGroupCommercial->setPlatform('official');

        $officialGroupFinance = new AdminPermissionGroups();
        $officialGroupFinance->setGroupKey('finance');
        $officialGroupFinance->setGroupName('财务');
        $officialGroupFinance->setPlatform('official');

        $officialPermissionDashboard = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_DASHBOARD,
            ));

        $officialPermissionBanner = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_BANNER,
            ));

        $officialPermissionNews = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_NEWS,
            ));

        $officialPermissionOrder = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ORDER,
            ));

        $officialPermissionUser = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_USER,
            ));

        $officialPermissionSales = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_SALES,
            ));

        $officialPermissionSpace = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_SPACE,
            ));

        $officialPermissionReserve = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ORDER_RESERVE,
            ));

        $officialPermissionPreOrder = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ORDER_PREORDER,
            ));

        $officialPermissionAdmin = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ADMIN,
            ));

        $officialPermissionLog = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_LOG,
            ));

        $officialPermissionAnnouncement = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ANNOUNCEMENT,
            ));

        $officialPermissionMessage = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_MESSAGE,
            ));

        $officialPermissionActivity = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_EVENT,
            ));

        $officialPermissionVerify = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_VERIFY,
            ));

        $officialPermissionInvoice = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_INVOICE,
            ));

        $officialPermissionBulletin = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_BULLETIN,
            ));

        $officialPermissionProductAppointment = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_PRODUCT_APPOINTMENT_VERIFY,
            ));

        $officialPermissionCommercial = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ADVERTISING,
            ));

        $officialPermissionFinance = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_FINANCE,
            ));

        $officialMap1 = new AdminPermissionGroupMap();
        $officialMap1->setPermission($officialPermissionDashboard);
        $officialMap1->setGroup($officialGroupDashboard);

        $officialMap2 = new AdminPermissionGroupMap();
        $officialMap2->setPermission($officialPermissionBanner);
        $officialMap2->setGroup($officialBanner);

        $officialMap3 = new AdminPermissionGroupMap();
        $officialMap3->setPermission($officialPermissionNews);
        $officialMap3->setGroup($officialGroupNews);

        $officialMap4 = new AdminPermissionGroupMap();
        $officialMap4->setPermission($officialPermissionOrder);
        $officialMap4->setGroup($officialGroupTrade);

        $officialMap5 = new AdminPermissionGroupMap();
        $officialMap5->setPermission($officialPermissionLease);
        $officialMap5->setGroup($officialGroupTrade);

        $officialMap6 = new AdminPermissionGroupMap();
        $officialMap6->setPermission($officialPermissionLongTermAppointment);
        $officialMap6->setGroup($officialGroupTrade);

        $officialMap7 = new AdminPermissionGroupMap();
        $officialMap7->setPermission($officialPermissionUser);
        $officialMap7->setGroup($officialGroupUser);

        $officialMap8 = new AdminPermissionGroupMap();
        $officialMap8->setPermission($officialPermissionSales);
        $officialMap8->setGroup($officialGroupSales);

        $officialMap9 = new AdminPermissionGroupMap();
        $officialMap9->setPermission($officialPermissionSpace);
        $officialMap9->setGroup($officialGroupSpace);

        $officialMap10 = new AdminPermissionGroupMap();
        $officialMap10->setPermission($officialPermissionReserve);
        $officialMap10->setGroup($officialGroupSpace);

        $officialMap11 = new AdminPermissionGroupMap();
        $officialMap11->setPermission($officialPermissionPreOrder);
        $officialMap11->setGroup($officialGroupSpace);

        $officialMap12 = new AdminPermissionGroupMap();
        $officialMap12->setPermission($officialPermissionOrder);
        $officialMap12->setGroup($officialGroupSpace);

        $officialMap13 = new AdminPermissionGroupMap();
        $officialMap13->setPermission($officialPermissionLease);
        $officialMap13->setGroup($officialGroupSpace);

        $officialMap14 = new AdminPermissionGroupMap();
        $officialMap14->setPermission($officialPermissionLongTermAppointment);
        $officialMap14->setGroup($officialGroupSpace);

        $officialMap15 = new AdminPermissionGroupMap();
        $officialMap15->setPermission($officialPermissionAdmin);
        $officialMap15->setGroup($officialGroupAdmin);

        $officialMap16 = new AdminPermissionGroupMap();
        $officialMap16->setPermission($officialPermissionLog);
        $officialMap16->setGroup($officialGroupLog);

        $officialMap17 = new AdminPermissionGroupMap();
        $officialMap17->setPermission($officialPermissionAnnouncement);
        $officialMap17->setGroup($officialGroupAnnouncement);

        $officialMap18 = new AdminPermissionGroupMap();
        $officialMap18->setPermission($officialPermissionMessage);
        $officialMap18->setGroup($officialGroupMessage);

        $officialMap19 = new AdminPermissionGroupMap();
        $officialMap19->setPermission($officialPermissionActivity);
        $officialMap19->setGroup($officialGroupActivity);

        $officialMap20 = new AdminPermissionGroupMap();
        $officialMap20->setPermission($officialPermissionVerify);
        $officialMap20->setGroup($officialGroupVerify);

        $officialMap21 = new AdminPermissionGroupMap();
        $officialMap21->setPermission($officialPermissionInvoice);
        $officialMap21->setGroup($officialGroupInvoice);

        $officialMap22 = new AdminPermissionGroupMap();
        $officialMap22->setPermission($officialPermissionBulletin);
        $officialMap22->setGroup($officialGroupBulletin);

        $officialMap23 = new AdminPermissionGroupMap();
        $officialMap23->setPermission($officialPermissionProductAppointment);
        $officialMap23->setGroup($officialGroupProductAppointment);

        $officialMap24 = new AdminPermissionGroupMap();
        $officialMap24->setPermission($officialPermissionCommercial);
        $officialMap24->setGroup($officialGroupCommercial);

        $officialMap25 = new AdminPermissionGroupMap();
        $officialMap25->setPermission($officialPermissionFinance);
        $officialMap25->setGroup($officialGroupFinance);

        $em->persist($officialGroupDashboard);
        $em->persist($officialBanner);
        $em->persist($officialGroupNews);
        $em->persist($officialGroupTrade);
        $em->persist($officialGroupUser);
        $em->persist($officialGroupSales);
        $em->persist($officialGroupSpace);
        $em->persist($officialGroupAdmin);
        $em->persist($officialGroupLog);
        $em->persist($officialGroupAnnouncement);
        $em->persist($officialGroupMessage);
        $em->persist($officialGroupActivity);
        $em->persist($officialGroupVerify);
        $em->persist($officialGroupInvoice);
        $em->persist($officialGroupBulletin);
        $em->persist($officialGroupProductAppointment);
        $em->persist($officialGroupCommercial);
        $em->persist($officialGroupFinance);
        $em->persist($officialPermissionLease);
        $em->persist($officialPermissionLongTermAppointment);
        $em->persist($officialMap1);
        $em->persist($officialMap2);
        $em->persist($officialMap3);
        $em->persist($officialMap4);
        $em->persist($officialMap5);
        $em->persist($officialMap6);
        $em->persist($officialMap7);
        $em->persist($officialMap8);
        $em->persist($officialMap9);
        $em->persist($officialMap10);
        $em->persist($officialMap11);
        $em->persist($officialMap12);
        $em->persist($officialMap13);
        $em->persist($officialMap14);
        $em->persist($officialMap15);
        $em->persist($officialMap16);
        $em->persist($officialMap17);
        $em->persist($officialMap18);
        $em->persist($officialMap19);
        $em->persist($officialMap20);
        $em->persist($officialMap21);
        $em->persist($officialMap22);
        $em->persist($officialMap23);
        $em->persist($officialMap24);
        $em->persist($officialMap25);

        // sales
        $permissionLease = new AdminPermission();
        $permissionLease->setKey(AdminPermission::KEY_SALES_BUILDING_LONG_TERM_LEASE);
        $permissionLease->setName('合同管理');
        $permissionLease->setLevel('specify');
        $permissionLease->setPlatform('sales');
        $permissionLease->setOpLevelSelect('1,2');
        $permissionLease->setMaxOpLevel('2');

        $permissionLongTermAppointment = new AdminPermission();
        $permissionLongTermAppointment->setKey(AdminPermission::KEY_SALES_BUILDING_LONG_TERM_APPOINTMENT);
        $permissionLongTermAppointment->setName('长租申请');
        $permissionLongTermAppointment->setLevel('specify');
        $permissionLongTermAppointment->setPlatform('sales');
        $permissionLongTermAppointment->setOpLevelSelect('1,2');
        $permissionLongTermAppointment->setMaxOpLevel('2');

        $groupDashboard = new AdminPermissionGroups();
        $groupDashboard->setGroupKey('dashboard');
        $groupDashboard->setGroupName('控制台');
        $groupDashboard->setPlatform('sales');

        $groupTrade = new AdminPermissionGroups();
        $groupTrade->setGroupKey('trade');
        $groupTrade->setGroupName('交易管理');
        $groupTrade->setPlatform('sales');

        $groupSpace = new AdminPermissionGroups();
        $groupSpace->setGroupKey('space');
        $groupSpace->setGroupName('空间管理');
        $groupSpace->setPlatform('sales');

        $groupUser = new AdminPermissionGroups();
        $groupUser->setGroupKey('user');
        $groupUser->setGroupName('用户管理');
        $groupUser->setPlatform('sales');

        $groupInvoice = new AdminPermissionGroups();
        $groupInvoice->setGroupKey('invoice');
        $groupInvoice->setGroupName('发票管理');
        $groupInvoice->setPlatform('sales');

        $groupAdmin = new AdminPermissionGroups();
        $groupAdmin->setGroupKey('admin');
        $groupAdmin->setGroupName('管理员');
        $groupAdmin->setPlatform('sales');

        $groupActivity = new AdminPermissionGroups();
        $groupActivity->setGroupKey('activity');
        $groupActivity->setGroupName('活动');
        $groupActivity->setPlatform('sales');

        $permissionDashboard = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SALES_PLATFORM_DASHBOARD,
            ));

        $permissionOrder = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SALES_BUILDING_ORDER,
            ));

        $permissionBuildingAdd = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SALES_PLATFORM_BUILDING,
            ));

        $permissionBuildingBuilding = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SALES_BUILDING_BUILDING,
            ));

        $permissionRoom = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SALES_BUILDING_ROOM,
            ));

        $permissionProduct = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SALES_BUILDING_PRODUCT,
            ));

        $permissionReserve = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SALES_BUILDING_ORDER_RESERVE,
            ));

        $permissionPreOrder = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SALES_BUILDING_ORDER_PREORDER,
            ));

        $permissionUser = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => 'sales.building.user',
            ));

        $permissionInvoice = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SALES_PLATFORM_INVOICE,
            ));

        $permissionAdmin = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SALES_PLATFORM_ADMIN,
            ));

        $permissionActivity = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SALES_PLATFORM_EVENT,
            ));

        $permissionSpace = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => 'sales.building.space',
            ));

        $map1 = new AdminPermissionGroupMap();
        $map1->setPermission($permissionDashboard);
        $map1->setGroup($groupDashboard);

        $map2 = new AdminPermissionGroupMap();
        $map2->setPermission($permissionOrder);
        $map2->setGroup($groupTrade);

        $map3 = new AdminPermissionGroupMap();
        $map3->setPermission($permissionLease);
        $map3->setGroup($groupTrade);

        $map4 = new AdminPermissionGroupMap();
        $map4->setPermission($permissionLongTermAppointment);
        $map4->setGroup($groupTrade);

        $map5 = new AdminPermissionGroupMap();
        $map5->setPermission($permissionBuildingAdd);
        $map5->setGroup($groupSpace);

        $map6 = new AdminPermissionGroupMap();
        $map6->setPermission($permissionBuildingBuilding);
        $map6->setGroup($groupSpace);

        $map7 = new AdminPermissionGroupMap();
        $map7->setPermission($permissionRoom);
        $map7->setGroup($groupSpace);

        $map8 = new AdminPermissionGroupMap();
        $map8->setPermission($permissionProduct);
        $map8->setGroup($groupSpace);

        $map9 = new AdminPermissionGroupMap();
        $map9->setPermission($permissionReserve);
        $map9->setGroup($groupSpace);

        $map10 = new AdminPermissionGroupMap();
        $map10->setPermission($permissionPreOrder);
        $map10->setGroup($groupSpace);

        $map11 = new AdminPermissionGroupMap();
        $map11->setPermission($permissionOrder);
        $map11->setGroup($groupSpace);

        $map12 = new AdminPermissionGroupMap();
        $map12->setPermission($permissionLease);
        $map12->setGroup($groupSpace);

        $map12_1 = new AdminPermissionGroupMap();
        $map12_1->setPermission($permissionLongTermAppointment);
        $map12_1->setGroup($groupSpace);

        $map13 = new AdminPermissionGroupMap();
        $map13->setPermission($permissionUser);
        $map13->setGroup($groupUser);

        $map14 = new AdminPermissionGroupMap();
        $map14->setPermission($permissionInvoice);
        $map14->setGroup($groupInvoice);

        $map15 = new AdminPermissionGroupMap();
        $map15->setPermission($permissionAdmin);
        $map15->setGroup($groupAdmin);

        $map16 = new AdminPermissionGroupMap();
        $map16->setPermission($permissionActivity);
        $map16->setGroup($groupActivity);

        $em->persist($permissionLongTermAppointment);
        $em->persist($permissionLease);
        $em->persist($groupDashboard);
        $em->persist($groupTrade);
        $em->persist($groupSpace);
        $em->persist($groupUser);
        $em->persist($groupInvoice);
        $em->persist($groupAdmin);
        $em->persist($groupActivity);
        $em->persist($map1);
        $em->persist($map2);
        $em->persist($map3);
        $em->persist($map4);
        $em->persist($map5);
        $em->persist($map6);
        $em->persist($map7);
        $em->persist($map8);
        $em->persist($map9);
        $em->persist($map10);
        $em->persist($map11);
        $em->persist($map12);
        $em->persist($map12_1);
        $em->persist($map13);
        $em->persist($map14);
        $em->persist($map15);
        $em->persist($map16);

        $em->remove($permissionSpace);

        // shop
        $shopGroupDashboard = new AdminPermissionGroups();
        $shopGroupDashboard->setGroupKey('dashboard');
        $shopGroupDashboard->setGroupName('控制台');
        $shopGroupDashboard->setPlatform('shop');

        $shopGroupOrder = new AdminPermissionGroups();
        $shopGroupOrder->setGroupKey('order');
        $shopGroupOrder->setGroupName('订单');
        $shopGroupOrder->setPlatform('shop');

        $shopGroupShop = new AdminPermissionGroups();
        $shopGroupShop->setGroupKey('shop');
        $shopGroupShop->setGroupName('店铺');
        $shopGroupShop->setPlatform('shop');

        $shopGroupSpecification = new AdminPermissionGroups();
        $shopGroupSpecification->setGroupKey('spec');
        $shopGroupSpecification->setGroupName('规格');
        $shopGroupSpecification->setPlatform('shop');

        $shopGroupProduct = new AdminPermissionGroups();
        $shopGroupProduct->setGroupKey('product');
        $shopGroupProduct->setGroupName('商品');
        $shopGroupProduct->setPlatform('shop');

        $shopGroupAdmin = new AdminPermissionGroups();
        $shopGroupAdmin->setGroupKey('admin');
        $shopGroupAdmin->setGroupName('管理员');
        $shopGroupAdmin->setPlatform('shop');

        $shopGroupKitchen = new AdminPermissionGroups();
        $shopGroupKitchen->setGroupKey('kitchen');
        $shopGroupKitchen->setGroupName('传菜系统');
        $shopGroupKitchen->setPlatform('shop');

        $shopPermissionDashboard = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SHOP_PLATFORM_DASHBOARD,
            ));

        $shopPermissionOrder = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SHOP_SHOP_ORDER,
            ));

        $shopPermissionShop = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SHOP_SHOP_SHOP,
            ));

        $shopPermissionSpec = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SHOP_PLATFORM_SPEC,
            ));

        $shopPermissionProduct = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SHOP_SHOP_PRODUCT,
            ));

        $shopPermissionAdmin = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SHOP_PLATFORM_ADMIN,
            ));

        $shopPermissionKitchen = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SHOP_SHOP_KITCHEN,
            ));

        $shopMap1 = new AdminPermissionGroupMap();
        $shopMap1->setPermission($shopPermissionDashboard);
        $shopMap1->setGroup($shopGroupDashboard);

        $shopMap2 = new AdminPermissionGroupMap();
        $shopMap2->setPermission($shopPermissionOrder);
        $shopMap2->setGroup($shopGroupOrder);

        $shopMap3 = new AdminPermissionGroupMap();
        $shopMap3->setPermission($shopPermissionShop);
        $shopMap3->setGroup($shopGroupShop);

        $shopMap4 = new AdminPermissionGroupMap();
        $shopMap4->setPermission($shopPermissionSpec);
        $shopMap4->setGroup($shopGroupSpecification);

        $shopMap5 = new AdminPermissionGroupMap();
        $shopMap5->setPermission($shopPermissionProduct);
        $shopMap5->setGroup($shopGroupProduct);

        $shopMap6 = new AdminPermissionGroupMap();
        $shopMap6->setPermission($shopPermissionAdmin);
        $shopMap6->setGroup($shopGroupAdmin);

        $shopMap7 = new AdminPermissionGroupMap();
        $shopMap7->setPermission($shopPermissionKitchen);
        $shopMap7->setGroup($shopGroupKitchen);

        $em->persist($shopGroupDashboard);
        $em->persist($shopGroupOrder);
        $em->persist($shopGroupShop);
        $em->persist($shopGroupSpecification);
        $em->persist($shopGroupProduct);
        $em->persist($shopGroupAdmin);
        $em->persist($shopGroupKitchen);
        $em->persist($shopMap1);
        $em->persist($shopMap2);
        $em->persist($shopMap3);
        $em->persist($shopMap4);
        $em->persist($shopMap5);
        $em->persist($shopMap6);
        $em->persist($shopMap7);

        $em->flush();
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
