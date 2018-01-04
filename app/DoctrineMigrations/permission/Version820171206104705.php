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
class Version820171206104705 extends AbstractMigration implements ContainerAwareInterface
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
                'groupKey' => 'setting',
                'platform' => 'sales',
            ));

        $logPermission = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key'=> AdminPermission::KEY_SALES_PLATFORM_LOG
            ));
        $buildingSettingPermission = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key'=> AdminPermission::KEY_SALES_BUILDING_SETTING
            ));

        $groupMap1 = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroupMap')
            ->findOneBy(array(
                'group' => $group,
                'permission' => $logPermission
            ));
        $groupMap2 = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroupMap')
            ->findOneBy(array(
                'group' => $group,
                'permission' => $buildingSettingPermission
            ));
        $em->remove($groupMap1);
        $em->remove($groupMap2);
        $em->remove($buildingSettingPermission);

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
