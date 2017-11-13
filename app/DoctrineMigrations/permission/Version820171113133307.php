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
class Version820171113133307 extends AbstractMigration implements ContainerAwareInterface
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

        $group = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => 'usage',
                'platform' => 'sales',
            ));

        $groupMaps = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroupMap')
            ->findBy(array(
                'group' => $group
            ));

        foreach($groupMaps as $groupMap){
            $em->remove($groupMap);
        }

        $em->flush();

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

        $gruopMap2 = new AdminPermissionGroupMap();
        $gruopMap2->setGroup($group);
        $gruopMap2->setPermission($InternalOccupancyPermission);

        $gruopMap3 = new AdminPermissionGroupMap();
        $gruopMap3->setGroup($group);
        $gruopMap3->setPermission($pushOrderPermission);

        $gruopMap4 = new AdminPermissionGroupMap();
        $gruopMap4->setGroup($group);
        $gruopMap4->setPermission($buildingRoomPermission);

        $em->persist($gruopMap2);
        $em->persist($gruopMap3);
        $em->persist($gruopMap4);

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
