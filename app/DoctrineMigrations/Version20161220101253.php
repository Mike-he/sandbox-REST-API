<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionGroupMap;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161220101253 extends AbstractMigration implements ContainerAwareInterface
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

        $shopOrderPermission = new AdminPermission();
        $shopOrderPermission->setKey(AdminPermission::KEY_OFFICIAL_PLATFORM_SHOP_ORDER);
        $shopOrderPermission->setName('店铺订单权限');
        $shopOrderPermission->setLevel('global');
        $shopOrderPermission->setPlatform('official');
        $shopOrderPermission->setOpLevelSelect('1');
        $shopOrderPermission->setMaxOpLevel('1');

        $topUpPermission = new AdminPermission();
        $topUpPermission->setKey(AdminPermission::KEY_OFFICIAL_PLATFORM_TOP_UP);
        $topUpPermission->setName('充值订单权限');
        $topUpPermission->setLevel('global');
        $topUpPermission->setPlatform('official');
        $topUpPermission->setOpLevelSelect('1');
        $topUpPermission->setMaxOpLevel('1');

        $tradeGroup = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => 'trade',
                'platform' => 'official',
            ));

        $map1 = new AdminPermissionGroupMap();
        $map1->setGroup($tradeGroup);
        $map1->setPermission($shopOrderPermission);

        $map2 = new AdminPermissionGroupMap();
        $map2->setGroup($tradeGroup);
        $map2->setPermission($topUpPermission);

        $em->persist($shopOrderPermission);
        $em->persist($topUpPermission);
        $em->persist($map1);
        $em->persist($map2);

        // official
        $permission1 = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_DASHBOARD,
            ));
        $permission1->setName('控制台权限');

        $permission2 = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_BANNER,
            ));
        $permission2->setName('横幅权限');

        $permission3 = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_NEWS,
            ));
        $permission3->setName('新闻权限');

        $permission4 = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ORDER,
            ));
        $permission4->setName('秒租订单权限');

        $permission5 = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_LONG_TERM_LEASE,
            ));
        $permission5->setName('长租合同权限');
        $permission5->setOpLevelSelect('1,2');
        $permission5->setMaxOpLevel(2);

        $permission6 = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_LONG_TERM_APPOINTMENT,
            ));
        $permission6->setName('长租申请权限');

        $permission7 = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_EVENT_ORDER,
            ));
        $permission7->setName('活动订单权限');

        $permission8 = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_USER,
            ));
        $permission8->setName('用户权限');

        $permission9 = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_SALES,
            ));
        $permission9->setName('销售方权限');

        $permission10 = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_SPACE,
            ));
        $permission10->setName('空间权限');

        $permission11 = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ORDER_PREORDER,
            ));
        $permission11->setName('预定权限');

        $permission11_1 = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ORDER_RESERVE,
            ));
        $permission11_1->setName('预留权限');

        $permission12 = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ADMIN,
            ));
        $permission12->setName('管理员权限');

        $permission13 = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_LOG,
            ));
        $permission13->setName('管理员日志权限');

        $permission13 = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_LOG,
            ));
        $permission13->setName('管理员日志权限');

        $permission14 = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ANNOUNCEMENT,
            ));
        $permission14->setName('通知权限');

        $permission15 = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_MESSAGE,
            ));
        $permission15->setName('消息权限');

        $permission16 = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_EVENT,
            ));
        $permission16->setName('活动权限');

        $permission17 = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_VERIFY,
            ));
        $permission17->setName('审查权限');

        $permission18 = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_INVOICE,
            ));
        $permission18->setName('发票权限');

        $permission19 = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_REFUND,
            ));
        $permission19->setName('退款权限');

        $permission20 = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_BULLETIN,
            ));
        $permission20->setName('创合说明权限');

        $permission21 = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_PRODUCT_APPOINTMENT_VERIFY,
            ));
        $permission21->setName('预约审核权限');

        $permission22 = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ADVERTISING,
            ));
        $permission22->setName('广告权限');

        $permission23 = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_FINANCE,
            ));
        $permission23->setName('财务权限');

        // sales
        $permission24 = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SALES_PLATFORM_DASHBOARD,
            ));
        $permission24->setName('控制台权限');

        $permission25 = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SALES_PLATFORM_DASHBOARD,
            ));
        $permission25->setName('控制台权限');

        $permission26 = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SALES_BUILDING_ORDER,
            ));
        $permission26->setName('秒租订单权限');

        $permission27 = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SALES_BUILDING_LONG_TERM_LEASE,
            ));
        $permission27->setName('长租合同权限');

        $permission28 = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SALES_BUILDING_LONG_TERM_APPOINTMENT,
            ));
        $permission28->setName('长租申请权限');

        $permission29 = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SALES_PLATFORM_EVENT_ORDER,
            ));
        $permission29->setName('活动订单权限');

        $permission30 = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SALES_BUILDING_BUILDING,
            ));
        $permission30->setName('社区权限');

        $permission31 = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SALES_BUILDING_ROOM,
            ));
        $permission31->setName('空间权限');

        $permission32 = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SALES_BUILDING_ORDER_PREORDER,
            ));
        $permission32->setName('预定权限');

        $permission34 = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SALES_BUILDING_USER,
            ));
        $permission34->setName('用户权限');

        $permission35 = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SALES_PLATFORM_INVOICE,
            ));
        $permission35->setName('发票权限');

        $permission36 = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SALES_PLATFORM_ADMIN,
            ));
        $permission36->setName('管理员权限');

        $permission37 = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SALES_PLATFORM_EVENT,
            ));
        $permission37->setName('活动权限');

        $permission37_1 = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SALES_BUILDING_ORDER_RESERVE,
            ));
        $permission37_1->setName('预留权限');

        $permission37_2 = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SALES_BUILDING_PRODUCT,
            ));
        $permission37_2->setName('租赁权限');

        // shop
        $permission38 = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SHOP_PLATFORM_DASHBOARD,
            ));
        $permission38->setName('控制台管理');

        $permission39 = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SHOP_SHOP_ORDER,
            ));
        $permission39->setName('订单权限');

        $permission40 = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SHOP_SHOP_SHOP,
            ));
        $permission40->setName('店铺权限');

        $permission41 = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SHOP_PLATFORM_SPEC,
            ));
        $permission41->setName('规格权限');

        $permission42 = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SHOP_SHOP_PRODUCT,
            ));
        $permission42->setName('商品权限');

        $permission43 = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SHOP_PLATFORM_ADMIN,
            ));
        $permission43->setName('管理员权限');

        $permission44 = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SHOP_SHOP_KITCHEN,
            ));
        $permission44->setName('传菜系统权限');

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
