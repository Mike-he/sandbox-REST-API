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
class Version920170309060001 extends AbstractMigration implements ContainerAwareInterface
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

        $salesGroup = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => AdminPermissionGroups::GROUP_KEY_SALES,
                'platform' => AdminPermissionGroups::GROUP_PLATFORM_OFFICIAL,
            ));

        $salesMonitoringPermission = new AdminPermission();
        $salesMonitoringPermission->setKey(AdminPermission::KEY_OFFICIAL_PLATFORM_SALES_MONITORING);
        $salesMonitoringPermission->setName('销售方监管');
        $salesMonitoringPermission->setPlatform(AdminPermission::PERMISSION_PLATFORM_OFFICIAL);
        $salesMonitoringPermission->setLevel(AdminPermission::PERMISSION_LEVEL_GLOBAL);
        $salesMonitoringPermission->setOpLevelSelect('1');
        $salesMonitoringPermission->setMaxOpLevel('1');
        $em->persist($salesMonitoringPermission);

        $map = new AdminPermissionGroupMap();
        $map->setGroup($salesGroup);
        $map->setPermission($salesMonitoringPermission);
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
