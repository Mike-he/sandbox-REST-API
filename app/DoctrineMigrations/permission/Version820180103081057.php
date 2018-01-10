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
class Version820180103081057 extends AbstractMigration implements ContainerAwareInterface
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

        // commnue_dashboard
        $permissionGroup1 = new AdminPermissionGroups();
        $permissionGroup1->setGroupKey('commnue_expert');
        $permissionGroup1->setGroupName('专家管理');
        $permissionGroup1->setPlatform('commnue');
        $em->persist($permissionGroup1);

        $permission1 = new AdminPermission();
        $permission1->setKey('commnue.platform.expert');
        $permission1->setName('专家管理');
        $permission1->setPlatform('commnue');
        $permission1->setLevel('global');
        $permission1->setOpLevelSelect('1,2');
        $permission1->setMaxOpLevel('2');
        $em->persist($permission1);

        $map1 = new AdminPermissionGroupMap();
        $map1->setGroup($permissionGroup1);
        $map1->setPermission($permission1);
        $em->persist($map1);

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
