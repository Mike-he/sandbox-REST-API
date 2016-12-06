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
class Version20161205062900 extends AbstractMigration implements ContainerAwareInterface
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
//        $officialPermissionLease = new AdminPermission();
//        $officialPermissionLease->setKey(AdminPermission::KEY_OFFICIAL_PLATFORM_LONG_TERM_LEASE);
//        $officialPermissionLease->setName('合同管理');
//        $officialPermissionLease->setLevel('global');
//        $officialPermissionLease->setPlatform('official');
//        $officialPermissionLease->setOpLevelSelect('1');
//        $officialPermissionLease->setMaxOpLevel('1');
//
//        $permissionLongTermAppointment = new AdminPermission();
//        $permissionLongTermAppointment->setKey(AdminPermission::KEY_OFFICIAL_PLATFORM_LONG_TERM_APPOINTMENT);
//        $permissionLongTermAppointment->setName('长租申请');
//        $permissionLongTermAppointment->setLevel('global');
//        $permissionLongTermAppointment->setPlatform('official');
//        $permissionLongTermAppointment->setOpLevelSelect('1');
//        $permissionLongTermAppointment->setMaxOpLevel('1');

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
        $groupDashboard->setGroupKey('sales_dashboard');

        $groupTrade = new AdminPermissionGroups();
        $groupTrade->setGroupKey('sales_trade');

        $groupSpace = new AdminPermissionGroups();
        $groupSpace->setGroupKey('sales_space');

        $groupUser = new AdminPermissionGroups();
        $groupUser->setGroupKey('sales_user');

        $groupInvoice = new AdminPermissionGroups();
        $groupInvoice->setGroupKey('sales_invoice');

        $groupAdmin = new AdminPermissionGroups();
        $groupAdmin->setGroupKey('sales_admin');

        $groupActivity = new AdminPermissionGroups();
        $groupActivity->setGroupKey('sales_activity');

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
                'key' => AdminPermission::KEY_SALES_BUILDING_USER,
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
