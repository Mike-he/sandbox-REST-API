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
class Version820161219165153 extends AbstractMigration implements ContainerAwareInterface
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
        $activityOrderPermission = new AdminPermission();
        $activityOrderPermission->setKey(AdminPermission::KEY_OFFICIAL_PLATFORM_EVENT_ORDER);
        $activityOrderPermission->setName('活动订单');
        $activityOrderPermission->setLevel('global');
        $activityOrderPermission->setPlatform('official');
        $activityOrderPermission->setOpLevelSelect('1');
        $activityOrderPermission->setMaxOpLevel('1');

        $tradeGroup = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => 'trade',
                'platform' => 'official',
            ));

        $groupMap = new AdminPermissionGroupMap();
        $groupMap->setGroup($tradeGroup);
        $groupMap->setPermission($activityOrderPermission);

        // sales
        $activityOrderPermissionSales = new AdminPermission();
        $activityOrderPermissionSales->setKey(AdminPermission::KEY_SALES_PLATFORM_EVENT_ORDER);
        $activityOrderPermissionSales->setName('活动订单');
        $activityOrderPermissionSales->setLevel('global');
        $activityOrderPermissionSales->setPlatform('sales');
        $activityOrderPermissionSales->setOpLevelSelect('1');
        $activityOrderPermissionSales->setMaxOpLevel('1');

        $tradeGroupSales = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => 'trade',
                'platform' => 'sales',
            ));

        $groupMapSales = new AdminPermissionGroupMap();
        $groupMapSales->setGroup($tradeGroupSales);
        $groupMapSales->setPermission($activityOrderPermissionSales);

        $em->persist($activityOrderPermission);
        $em->persist($activityOrderPermissionSales);
        $em->persist($groupMap);
        $em->persist($groupMapSales);

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
