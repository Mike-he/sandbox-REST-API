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
class Version820171027150107 extends AbstractMigration implements ContainerAwareInterface
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

        $tradeGroup = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => 'dashboard',
                'platform' => 'sales',
            ));

        $orderPermission = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findBy(array(
                'key'=> AdminPermission::KEY_SALES_BUILDING_ORDER
            ));
        $groupMap1 = new AdminPermissionGroupMap();
        $groupMap1->setGroup($tradeGroup);
        $groupMap1->setPermission($orderPermission);

        $billPermission = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findBy(array(
                'key'=> AdminPermission::KEY_SALES_BUILDING_LEASE_BILL
            ));
        $groupMap2 = new AdminPermissionGroupMap();
        $groupMap2->setGroup($tradeGroup);
        $groupMap2->setPermission($billPermission);

        $leasePermission = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findBy(array(
                'key'=> AdminPermission::KEY_SALES_BUILDING_LONG_TERM_LEASE
            ));
        $groupMap3 = new AdminPermissionGroupMap();
        $groupMap3->setGroup($tradeGroup);
        $groupMap3->setPermission($leasePermission);

        $cluePermission = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findBy(array(
                'key'=> AdminPermission::KEY_SALES_BUILDING_LEASE_CLUE
            ));
        $groupMap4 = new AdminPermissionGroupMap();
        $groupMap4->setGroup($tradeGroup);
        $groupMap4->setPermission($cluePermission);

        $eventOrderPermission = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findBy(array(
                'key'=> AdminPermission::KEY_SALES_PLATFORM_EVENT_ORDER
            ));
        $groupMap5 = new AdminPermissionGroupMap();
        $groupMap5->setGroup($tradeGroup);
        $groupMap5->setPermission($eventOrderPermission);

        $membershipOrderPermission = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findBy(array(
                'key'=> AdminPermission::KEY_SALES_PLATFORM_MEMBERSHIP_CARD_ORDER
            ));
        $groupMap6 = new AdminPermissionGroupMap();
        $groupMap6->setGroup($tradeGroup);
        $groupMap6->setPermission($membershipOrderPermission);

        $em->persist($groupMap1);
        $em->persist($groupMap2);
        $em->persist($groupMap3);
        $em->persist($groupMap4);
        $em->persist($groupMap5);
        $em->persist($groupMap6);

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
