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
class Version820171114094706 extends AbstractMigration implements ContainerAwareInterface
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

        $internalOccupancyPermission = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SALES_BUILDING_INTERNAL_OCCUPANCY
            ));
        if($internalOccupancyPermission){
            $internalOccupancyPermissionGroupMaps = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroupMap')
                ->findBy(array(
                    'permission' => $internalOccupancyPermission
                ));
            $buildingOrderReservePermission = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
                ->findOneBy(array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_ORDER_RESERVE
                ));
            foreach ($internalOccupancyPermissionGroupMaps as $item){
                $item->setPermission($buildingOrderReservePermission);
            }

            $em->remove($internalOccupancyPermission);

            $buildingOrderReservePermission->setName('设置内部占用权限');
        }

        $pushOrderPermission = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SALES_BUILDING_PUSH_ORDER
            ));
        if($pushOrderPermission){
            $pushOrderPermissionGroupMaps = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroupMap')
                ->findBy(array(
                    'permission' => $pushOrderPermission
                ));
            $buildingOrderPreorderPermission = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
                ->findOneBy(array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_ORDER_PREORDER
                ));
            foreach ($pushOrderPermissionGroupMaps as $item){
                $item->setPermission($buildingOrderPreorderPermission);
            }

            $em->remove($pushOrderPermission);

            $buildingOrderPreorderPermission->setName('后台推送订单权限');
        }

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
