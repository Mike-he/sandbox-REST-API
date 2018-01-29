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
class Version820171128080907 extends AbstractMigration implements ContainerAwareInterface
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
        $permissionGroup1->setGroupKey('commnue_dashboard');
        $permissionGroup1->setGroupName('主页');
        $permissionGroup1->setPlatform('commnue');
        $em->persist($permissionGroup1);

        $permission1 = new AdminPermission();
        $permission1->setKey('commnue.platform.dashboard');
        $permission1->setName('主页');
        $permission1->setPlatform('commnue');
        $permission1->setLevel('global');
        $permission1->setOpLevelSelect('1');
        $permission1->setMaxOpLevel('1');
        $em->persist($permission1);

        $map1 = new AdminPermissionGroupMap();
        $map1->setGroup($permissionGroup1);
        $map1->setPermission($permission1);
        $em->persist($map1);

        // commnue_banner
        $permissionGroup2 = new AdminPermissionGroups();
        $permissionGroup2->setGroupKey('commnue_banner');
        $permissionGroup2->setGroupName('广告展示');
        $permissionGroup2->setPlatform('commnue');
        $em->persist($permissionGroup2);

        $permission21 = new AdminPermission();
        $permission21->setKey('commnue.platform.banner');
        $permission21->setName('轮播banner设置');
        $permission21->setPlatform('commnue');
        $permission21->setLevel('global');
        $permission21->setOpLevelSelect('1,2');
        $permission21->setMaxOpLevel('2');
        $em->persist($permission21);

        $permission22 = new AdminPermission();
        $permission22->setKey('commnue.platform.advertisement');
        $permission22->setName('中部广告位设置');
        $permission22->setPlatform('commnue');
        $permission22->setLevel('global');
        $permission22->setOpLevelSelect('1,2');
        $permission22->setMaxOpLevel('2');
        $em->persist($permission22);

        $permission23 = new AdminPermission();
        $permission23->setKey('commnue.platform.top');
        $permission23->setName('微头条');
        $permission23->setPlatform('commnue');
        $permission23->setLevel('global');
        $permission23->setOpLevelSelect('1,2');
        $permission23->setMaxOpLevel('2');
        $em->persist($permission23);

        $permission24 = new AdminPermission();
        $permission24->setKey('commnue.platform.material');
        $permission24->setName('素材管理');
        $permission24->setPlatform('commnue');
        $permission24->setLevel('global');
        $permission24->setOpLevelSelect('1,2');
        $permission24->setMaxOpLevel('2');
        $em->persist($permission24);

        $permission25 = new AdminPermission();
        $permission25->setKey('commnue.platform.screen');
        $permission25->setName('开屏广告');
        $permission25->setPlatform('commnue');
        $permission25->setLevel('global');
        $permission25->setOpLevelSelect('1,2');
        $permission25->setMaxOpLevel('2');
        $em->persist($permission25);

        $map21 = new AdminPermissionGroupMap();
        $map21->setGroup($permissionGroup2);
        $map21->setPermission($permission21);
        $em->persist($map21);

        $map22 = new AdminPermissionGroupMap();
        $map22->setGroup($permissionGroup2);
        $map22->setPermission($permission22);
        $em->persist($map22);

        $map23 = new AdminPermissionGroupMap();
        $map23->setGroup($permissionGroup2);
        $map23->setPermission($permission23);
        $em->persist($map23);

        $map24 = new AdminPermissionGroupMap();
        $map24->setGroup($permissionGroup2);
        $map24->setPermission($permission24);
        $em->persist($map24);

        $map25 = new AdminPermissionGroupMap();
        $map25->setGroup($permissionGroup2);
        $map25->setPermission($permission25);
        $em->persist($map25);

        //commnue_community
        $permissionGroup3 = new AdminPermissionGroups();
        $permissionGroup3->setGroupKey('commnue_community');
        $permissionGroup3->setGroupName('机构管理');
        $permissionGroup3->setPlatform('commnue');
        $em->persist($permissionGroup3);

        $permission3 = new AdminPermission();
        $permission3->setKey('commnue.platform.community');
        $permission3->setName('机构管理');
        $permission3->setPlatform('commnue');
        $permission3->setLevel('global');
        $permission3->setOpLevelSelect('1,2');
        $permission3->setMaxOpLevel('2');
        $em->persist($permission3);

        $map3 = new AdminPermissionGroupMap();
        $map3->setGroup($permissionGroup3);
        $map3->setPermission($permission3);
        $em->persist($map3);


        //commnue_user
        $permissionGroup4 = new AdminPermissionGroups();
        $permissionGroup4->setGroupKey('commnue_user');
        $permissionGroup4->setGroupName('用户管理');
        $permissionGroup4->setPlatform('commnue');
        $em->persist($permissionGroup4);

        $permission4 = new AdminPermission();
        $permission4->setKey('commnue.platform.user');
        $permission4->setName('用户管理');
        $permission4->setPlatform('commnue');
        $permission4->setLevel('global');
        $permission4->setOpLevelSelect('1,2');
        $permission4->setMaxOpLevel('2');
        $em->persist($permission4);

        $map4 = new AdminPermissionGroupMap();
        $map4->setGroup($permissionGroup4);
        $map4->setPermission($permission4);
        $em->persist($map4);

        //commnue_event
        $permissionGroup5 = new AdminPermissionGroups();
        $permissionGroup5->setGroupKey('commnue_event');
        $permissionGroup5->setGroupName('活动管理');
        $permissionGroup5->setPlatform('commnue');
        $em->persist($permissionGroup5);

        $permission5 = new AdminPermission();
        $permission5->setKey('commnue.platform.event');
        $permission5->setName('活动管理');
        $permission5->setPlatform('commnue');
        $permission5->setLevel('global');
        $permission5->setOpLevelSelect('1,2');
        $permission5->setMaxOpLevel('2');
        $em->persist($permission5);

        $map5 = new AdminPermissionGroupMap();
        $map5->setGroup($permissionGroup5);
        $map5->setPermission($permission5);
        $em->persist($map5);

        //commnue_admin
        $permissionGroup6 = new AdminPermissionGroups();
        $permissionGroup6->setGroupKey('commnue_admin');
        $permissionGroup6->setGroupName('管理员');
        $permissionGroup6->setPlatform('commnue');
        $em->persist($permissionGroup6);

        $permission6 = new AdminPermission();
        $permission6->setKey('commnue.platform.admin');
        $permission6->setName('管理员');
        $permission6->setPlatform('commnue');
        $permission6->setLevel('global');
        $permission6->setOpLevelSelect('1,2');
        $permission6->setMaxOpLevel('2');
        $em->persist($permission6);

        $map6 = new AdminPermissionGroupMap();
        $map6->setGroup($permissionGroup6);
        $map6->setPermission($permission6);
        $em->persist($map6);

        //commnue_customer
        $permissionGroup7 = new AdminPermissionGroups();
        $permissionGroup7->setGroupKey('commnue_customer');
        $permissionGroup7->setGroupName('客服');
        $permissionGroup7->setPlatform('commnue');
        $em->persist($permissionGroup7);

        $permission7 = new AdminPermission();
        $permission7->setKey('commnue.platform.customer');
        $permission7->setName('客服');
        $permission7->setPlatform('commnue');
        $permission7->setLevel('global');
        $permission7->setOpLevelSelect('1,2');
        $permission7->setMaxOpLevel('2');
        $em->persist($permission7);

        $map7 = new AdminPermissionGroupMap();
        $map7->setGroup($permissionGroup7);
        $map7->setPermission($permission7);
        $em->persist($map7);

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
