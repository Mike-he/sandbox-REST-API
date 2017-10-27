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

        $tradeGroup2 = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => 'space',
                'platform' => 'sales',
            ));

        $gruopMap2 = new AdminPermissionGroupMap();
        $gruopMap2->setGroup($tradeGroup2);
        $gruopMap2->setPermission($pushOrderPermission);

        $logPermission = new AdminPermission();
        $logPermission->setKey(AdminPermission::KEY_SALES_PLATFORM_LOG);
        $logPermission->setName('管理员日志权限');
        $logPermission->setLevel('global');
        $logPermission->setPlatform('sales');
        $logPermission->setOpLevelSelect('1');
        $logPermission->setMaxOpLevel('1');

        $tradeGroup3 = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => 'setting',
                'platform' => 'sales',
            ));

        $gruopMap3 = new AdminPermissionGroupMap();
        $gruopMap3->setGroup($tradeGroup3);
        $gruopMap3->setPermission($pushOrderPermission);

        $projectAddPermission = new AdminPermission();
        $projectAddPermission->setKey(AdminPermission::KEY_SALES_PLATFORM_PROJECT_ADD);
        $projectAddPermission->setName('管理员日志权限');
        $projectAddPermission->setLevel('global');
        $projectAddPermission->setPlatform('sales');
        $projectAddPermission->setOpLevelSelect('1');
        $projectAddPermission->setMaxOpLevel('1');

        $tradeGroup3 = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => 'setting',
                'platform' => 'sales',
            ));

        $gruopMap3 = new AdminPermissionGroupMap();
        $gruopMap3->setGroup($tradeGroup3);
        $gruopMap3->setPermission($pushOrderPermission);

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
