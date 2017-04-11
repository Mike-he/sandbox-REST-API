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
class Version920170406084749 extends AbstractMigration implements ContainerAwareInterface
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

        $em = $this->container->get('doctrine.orm.entity_manager');

        $salesCardGroup = new AdminPermissionGroups();
        $salesCardGroup->setGroupName('会员卡管理');
        $salesCardGroup->setGroupKey('membership');
        $salesCardGroup->setPlatform('sales');
        $em->persist($salesCardGroup);

        $salesTradeGroup = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => AdminPermissionGroups::GROUP_KEY_TRADE,
                'platform' => AdminPermissionGroups::GROUP_PLATFORM_SALES,
            ));

        $officialTradeGroup = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => AdminPermissionGroups::GROUP_KEY_TRADE,
                'platform' => AdminPermissionGroups::GROUP_PLATFORM_OFFICIAL,
            ));

        $officialCardOrderPermission = new AdminPermission();
        $officialCardOrderPermission->setKey(AdminPermission::KEY_OFFICIAL_PLATFORM_MEMBERSHIP_CARD_ORDER);
        $officialCardOrderPermission->setName('会员卡订单权限');
        $officialCardOrderPermission->setPlatform(AdminPermission::PERMISSION_PLATFORM_OFFICIAL);
        $officialCardOrderPermission->setLevel(AdminPermission::PERMISSION_LEVEL_GLOBAL);
        $officialCardOrderPermission->setOpLevelSelect('1');
        $officialCardOrderPermission->setMaxOpLevel('1');
        $em->persist($officialCardOrderPermission);

        $salesCardPermission = new AdminPermission();
        $salesCardPermission->setKey(AdminPermission::KEY_SALES_PLATFORM_MEMBERSHIP_CARD);
        $salesCardPermission->setName('会员卡权限');
        $salesCardPermission->setPlatform(AdminPermission::PERMISSION_PLATFORM_SALES);
        $salesCardPermission->setLevel(AdminPermission::PERMISSION_LEVEL_GLOBAL);
        $salesCardPermission->setOpLevelSelect('1,2');
        $salesCardPermission->setMaxOpLevel('2');
        $em->persist($salesCardPermission);

        $salesCardProductPermission = new AdminPermission();
        $salesCardProductPermission->setKey(AdminPermission::KEY_SALES_PLATFORM_MEMBERSHIP_CARD_PRODUCT);
        $salesCardProductPermission->setName('售卖权限');
        $salesCardProductPermission->setPlatform(AdminPermission::PERMISSION_PLATFORM_SALES);
        $salesCardProductPermission->setLevel(AdminPermission::PERMISSION_LEVEL_GLOBAL);
        $salesCardProductPermission->setOpLevelSelect('1,2');
        $salesCardProductPermission->setMaxOpLevel('2');
        $em->persist($salesCardProductPermission);

        $salesCardOrderPermission = new AdminPermission();
        $salesCardOrderPermission->setKey(AdminPermission::KEY_SALES_PLATFORM_MEMBERSHIP_CARD_ORDER);
        $salesCardOrderPermission->setName('会员卡订单权限');
        $salesCardOrderPermission->setPlatform(AdminPermission::PERMISSION_PLATFORM_SALES);
        $salesCardOrderPermission->setLevel(AdminPermission::PERMISSION_LEVEL_GLOBAL);
        $salesCardOrderPermission->setOpLevelSelect('1');
        $salesCardOrderPermission->setMaxOpLevel('1');
        $em->persist($salesCardOrderPermission);

        $map1 = new AdminPermissionGroupMap();
        $map1->setGroup($officialTradeGroup);
        $map1->setPermission($officialCardOrderPermission);
        $em->persist($map1);

        $map2 = new AdminPermissionGroupMap();
        $map2->setGroup($salesTradeGroup);
        $map2->setPermission($salesCardOrderPermission);
        $em->persist($map2);

        $map3 = new AdminPermissionGroupMap();
        $map3->setGroup($salesCardGroup);
        $map3->setPermission($salesCardPermission);
        $em->persist($map3);

        $map4 = new AdminPermissionGroupMap();
        $map4->setGroup($salesCardGroup);
        $map4->setPermission($salesCardProductPermission);
        $em->persist($map4);

        $map3 = new AdminPermissionGroupMap();
        $map3->setGroup($salesCardGroup);
        $map3->setPermission($salesCardOrderPermission);
        $em->persist($map3);

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
