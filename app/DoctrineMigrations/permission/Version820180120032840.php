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
class Version820180120032840 extends AbstractMigration implements ContainerAwareInterface
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
        parent::postUp($schema);

        /** @var EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');

        $permissionGroup = new AdminPermissionGroups();
        $permissionGroup->setGroupKey('commnue_company');
        $permissionGroup->setGroupName('企业管理');
        $permissionGroup->setPlatform('commnue');
        $em->persist($permissionGroup);

        $permission = new AdminPermission();
        $permission->setName('企业管理');
        $permission->setKey('commue.platform.company');
        $permission->setPlatform('commnue');
        $permission->setLevel('global');
        $permission->setOpLevelSelect('1');
        $permission->setMaxOpLevel('1');
        $em->persist($permission);

        $map = new AdminPermissionGroupMap();
        $map->setGroup($permissionGroup);
        $map->setPermission($permission);
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
