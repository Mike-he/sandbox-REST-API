<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionGroupMap;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionGroups;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161209160419 extends AbstractMigration implements ContainerAwareInterface
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

        $p1 = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => 'banner',
                'platform' => 'official',
            ));
        $p1->setGroupName('横幅管理');

        $p2 = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => 'news',
                'platform' => 'official',
            ));
        $p2->setGroupName('新闻管理');

        $p3 = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => 'sales',
                'platform' => 'official',
            ));
        $p3->setGroupName('销售方管理');

        $p4 = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => 'admin',
                'platform' => 'official',
            ));
        $p4->setGroupName('管理员管理');

        $p5 = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => 'log',
                'platform' => 'official',
            ));
        $p5->setGroupName('管理员日志管理');

        $p6 = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => 'announcement',
                'platform' => 'official',
            ));
        $p6->setGroupName('通知管理');

        $p7 = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => 'message',
                'platform' => 'official',
            ));
        $p7->setGroupName('消息管理');

        $p8 = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => 'activity',
                'platform' => 'official',
            ));
        $p8->setGroupName('活动管理');

        $p9 = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => 'verify',
                'platform' => 'official',
            ));
        $p9->setGroupName('审查管理');

        $p10 = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => 'bulletin',
                'platform' => 'official',
            ));
        $p10->setGroupName('创合说明发布管理');

        $p11 = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => 'product_appointment',
                'platform' => 'official',
            ));
        $p11->setGroupName('预约审核管理');

        $p12 = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => 'commercial',
                'platform' => 'official',
            ));
        $p12->setGroupName('广告管理');

        $p13 = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => 'finance',
                'platform' => 'official',
            ));
        $p13->setGroupName('财务管理');

        $p14 = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => 'dashboard',
                'platform' => 'sales',
            ));
        $p14->setGroupName('控制台管理');

        $p15 = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => 'admin',
                'platform' => 'sales',
            ));
        $p15->setGroupName('管理员管理');

        $p16 = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => 'activity',
                'platform' => 'sales',
            ));
        $p16->setGroupName('活动管理');

        $p17 = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => 'dashboard',
                'platform' => 'shop',
            ));
        $p17->setGroupName('控制台管理');

        $p19 = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => 'order',
                'platform' => 'shop',
            ));
        $p19->setGroupName('订单管理');

        $p20 = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => 'shop',
                'platform' => 'shop',
            ));
        $p20->setGroupName('店铺管理');

        $p21 = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => 'spec',
                'platform' => 'shop',
            ));
        $p21->setGroupName('规格管理');

        $p22 = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => 'product',
                'platform' => 'shop',
            ));
        $p22->setGroupName('商品管理');

        $p23 = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => 'admin',
                'platform' => 'shop',
            ));
        $p23->setGroupName('管理员管理');

        $refundGroup = new AdminPermissionGroups();
        $refundGroup->setGroupKey('refund');
        $refundGroup->setGroupName('退款管理');
        $refundGroup->setPlatform('official');

        $refundPermission = $em
            ->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_REFUND,
            ));

        $map = new AdminPermissionGroupMap();
        $map->setPermission($refundPermission);
        $map->setGroup($refundGroup);

        $em->persist($refundGroup);
        $em->persist($map);

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
