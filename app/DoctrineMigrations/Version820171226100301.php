<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionGroupMap;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionGroups;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version820171226100301 extends AbstractMigration implements ContainerAwareInterface
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
        /** @var EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');

        $group = new AdminPermissionGroups();
        $group->setGroupKey(AdminPermissionGroups::GROUP_KEY_SERVICE);
        $group->setGroupName('服务管理');
        $group->setPlatform(AdminPermissionGroups::GROUP_PLATFORM_SALES);
        $em->persist($group);

        $permission = new AdminPermission();
        $permission->setPlatform(AdminPermission::PERMISSION_PLATFORM_SALES);
        $permission->setKey(AdminPermission::KEY_SALES_PLATFORM_SERVICE);
        $permission->setName('服务管理权限');
        $permission->setLevel(AdminPermission::PERMISSION_LEVEL_GLOBAL);
        $permission->setMaxOpLevel(2);
        $permission->setOpLevelSelect('1,2');
        $em->persist($permission);

        $map = new AdminPermissionGroupMap();
        $map->setGroup($group);
        $map->setPermission($permission);
        $em->persist($map);


        $orderPermission = new AdminPermission();
        $orderPermission->setPlatform(AdminPermission::PERMISSION_PLATFORM_SALES);
        $orderPermission->setKey(AdminPermission::KEY_SALES_PLATFORM_SERVICE_ORDER);
        $orderPermission->setName('服务订单权限');
        $orderPermission->setLevel(AdminPermission::PERMISSION_LEVEL_GLOBAL);
        $orderPermission->setMaxOpLevel(1);
        $orderPermission->setOpLevelSelect(1);
        $em->persist($orderPermission);

        $orderGroup = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey'=> AdminPermissionGroups::GROUP_KEY_TRADE,
                'platform'=> AdminPermissionGroups::GROUP_PLATFORM_SALES,
            ));

        $orderMap = new AdminPermissionGroupMap();
        $orderMap->setGroup($orderGroup);
        $orderMap->setPermission($orderPermission);
        $em->persist($orderMap);

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
