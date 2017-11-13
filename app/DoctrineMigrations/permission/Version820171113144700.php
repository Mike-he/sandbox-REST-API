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
class Version820171113144700 extends AbstractMigration implements ContainerAwareInterface
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

        $buildingSettingPermission = new AdminPermission();
        $buildingSettingPermission->setKey(AdminPermission::KEY_SALES_BUILDING_SETTING);
        $buildingSettingPermission->setName('空间设置');
        $buildingSettingPermission->setLevel('specify');
        $buildingSettingPermission->setPlatform('sales');
        $buildingSettingPermission->setOpLevelSelect('2');
        $buildingSettingPermission->setMaxOpLevel('2');

        $tradeGroup1 = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => 'setting',
                'platform' => 'sales',
            ));
        $gruopMap = new AdminPermissionGroupMap();
        $gruopMap->setGroup($tradeGroup1);
        $gruopMap->setPermission($buildingSettingPermission);
        $em->persist($gruopMap);

        $group = new AdminPermissionGroups();
        $group->setGroupKey('usage');
        $group->setGroupName('空间管理');
        $group->setPlatform('sales');
        $em->persist($group);

        $InternalOccupancyPermission = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key'=> AdminPermission::KEY_SALES_BUILDING_INTERNAL_OCCUPANCY
            ));
        $pushOrderPermission = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key'=> AdminPermission::KEY_SALES_BUILDING_PUSH_ORDER
            ));
        $buildingRoomPermission = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key'=> AdminPermission::KEY_SALES_BUILDING_ROOM
            ));
        $spaceGroup = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey'=> 'space',
                'platform' => 'sales'
            ));

        $gruopMap1 = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroupMap')
            ->findOneBy(array(
                'group' => $spaceGroup,
                'permission' => $InternalOccupancyPermission
            ));
        $gruopMap1->setGroup($group);

        $gruopMap2 = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroupMap')
            ->findOneBy(array(
                'group' => $spaceGroup,
                'permission' => $pushOrderPermission
            ));
        $gruopMap2->setGroup($group);

        $gruopMap3 = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroupMap')
            ->findOneBy(array(
                'group' => $spaceGroup,
                'permission' => $buildingRoomPermission
            ));
        $gruopMap3->setGroup($group);

        $em->persist($buildingSettingPermission);
        
        $em->persist($gruopMap1);
        $em->persist($gruopMap2);
        $em->persist($gruopMap3);

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
