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
class Version820171113105307 extends AbstractMigration implements ContainerAwareInterface
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
        $gruopMap1 = new AdminPermissionGroupMap();
        $gruopMap1->setGroup($tradeGroup1);
        $gruopMap1->setPermission($buildingSettingPermission);

        $tradeGroup2 = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => 'space',
                'platform' => 'sales',
            ));
        $tradeGroup2->setGroupName('空间管理');
        $tradeGroup2->setGroupKey('usage');

        $em->persist($buildingSettingPermission);
        $em->persist($gruopMap1);

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
