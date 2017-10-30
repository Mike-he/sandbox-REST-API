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
class Version820171027174810 extends AbstractMigration implements ContainerAwareInterface
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

        $InternalOccupancyPermission = new AdminPermission();
        $InternalOccupancyPermission->setKey(AdminPermission::KEY_SALES_BUILDING_INTERNAL_OCCUPANCY);
        $InternalOccupancyPermission->setName('设置内部占用权限');
        $InternalOccupancyPermission->setLevel('specify');
        $InternalOccupancyPermission->setPlatform('sales');
        $InternalOccupancyPermission->setOpLevelSelect('2');
        $InternalOccupancyPermission->setMaxOpLevel('2');

        $tradeGroup1 = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => 'space',
                'platform' => 'sales',
            ));

        $gruopMap1 = new AdminPermissionGroupMap();
        $gruopMap1->setGroup($tradeGroup1);
        $gruopMap1->setPermission($InternalOccupancyPermission);

        $pushOrderPermission = new AdminPermission();
        $pushOrderPermission->setKey(AdminPermission::KEY_SALES_BUILDING_PUSH_ORDER);
        $pushOrderPermission->setName('后台推送订单权限');
        $pushOrderPermission->setLevel('specify');
        $pushOrderPermission->setPlatform('sales');
        $pushOrderPermission->setOpLevelSelect('2');
        $pushOrderPermission->setMaxOpLevel('2');

        $gruopMap2 = new AdminPermissionGroupMap();
        $gruopMap2->setGroup($tradeGroup1);
        $gruopMap2->setPermission($pushOrderPermission);

        $logPermission = new AdminPermission();
        $logPermission->setKey(AdminPermission::KEY_SALES_PLATFORM_LOG);
        $logPermission->setName('管理员日志权限');
        $logPermission->setLevel('global');
        $logPermission->setPlatform('sales');
        $logPermission->setOpLevelSelect('1');
        $logPermission->setMaxOpLevel('1');

        $tradeGroup2 = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => 'setting',
                'platform' => 'sales',
            ));

        $gruopMap3 = new AdminPermissionGroupMap();
        $gruopMap3->setGroup($tradeGroup2);
        $gruopMap3->setPermission($logPermission);

        $projectAddPermission = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SALES_PLATFORM_BUILDING
            ));

        $projectAddPermission->setName('项目新增');

        $gruopMap4 = new AdminPermissionGroupMap();
        $gruopMap4->setGroup($tradeGroup2);
        $gruopMap4->setPermission($projectAddPermission);

        $membershipPermission = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SALES_PLATFORM_MEMBERSHIP_CARD
            ));

        $gruopMap5 = new AdminPermissionGroupMap();
        $gruopMap5->setGroup($tradeGroup2);
        $gruopMap5->setPermission($membershipPermission);

        $spacePermission = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SALES_BUILDING_ROOM
            ));

        $gruopMap6 = new AdminPermissionGroupMap();
        $gruopMap6->setGroup($tradeGroup2);
        $gruopMap6->setPermission($spacePermission);

        $projectPermission = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SALES_BUILDING_BUILDING
            ));
        $projectPermission->setName('项目权限');

        $gruopMap7 = new AdminPermissionGroupMap();
        $gruopMap7->setGroup($tradeGroup2);
        $gruopMap7->setPermission($projectPermission);

        $customerPermission = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SALES_PLATFORM_CUSTOMER
            ));
        $customerPermission->setLevel('global');

        $em->persist($InternalOccupancyPermission);
        $em->persist($pushOrderPermission);
        $em->persist($logPermission);

        $em->persist($gruopMap1);
        $em->persist($gruopMap2);
        $em->persist($gruopMap3);
        $em->persist($gruopMap4);
        $em->persist($gruopMap5);
        $em->persist($gruopMap6);
        $em->persist($gruopMap7);

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
