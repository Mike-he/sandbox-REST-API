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
class Version920170211022954 extends AbstractMigration implements ContainerAwareInterface
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

        $salesTradeGroup = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => AdminPermissionGroups::GROUP_KEY_TRADE,
                'platform' => AdminPermissionGroups::GROUP_PLATFORM_SALES,
            ));

        $salesAuditPermission = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SALES_PLATFORM_AUDIT,
            ));

        $map1 = new AdminPermissionGroupMap();
        $map1->setGroup($salesTradeGroup);
        $map1->setPermission($salesAuditPermission);
        $em->persist($map1);

        $officialInvoiceGroup = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => 'invoice',
                'platform' => AdminPermissionGroups::GROUP_PLATFORM_OFFICIAL,
            ));
        $officialFinanceGroup = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => AdminPermissionGroups::GROUP_KEY_FINANCE,
                'platform' => AdminPermissionGroups::GROUP_PLATFORM_OFFICIAL,
            ));
        $officialRefundGroup = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => AdminPermissionGroups::GROUP_KEY_REFUND,
                'platform' => AdminPermissionGroups::GROUP_PLATFORM_OFFICIAL,
            ));

        $em->remove($officialInvoiceGroup);
        $em->remove($officialRefundGroup);

        $officialWithdrawalPermission = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_WITHDRAWAL,
            ));
        $officialWithdrawalPermission->setName('销售方提现处理权限');

        $officialFinancePermission = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_FINANCE,
            ));
        $officialFinancePermission->setName('财务统计权限');

        $officialInvoicePermission = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_INVOICE,
            ));

        $officialRefundPermission = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_REFUND,
            ));

        $permission1 = new AdminPermission();
        $permission1->setKey(AdminPermission::KEY_OFFICIAL_PLATFORM_LONG_TERM_SERVICE_RECEIPT);
        $permission1->setName('长租服务费收款权限');
        $permission1->setPlatform(AdminPermission::PERMISSION_PLATFORM_OFFICIAL);
        $permission1->setLevel(AdminPermission::PERMISSION_LEVEL_GLOBAL);
        $permission1->setOpLevelSelect('1,2');
        $permission1->setMaxOpLevel('2');
        $em->persist($permission1);

        $permission2 = new AdminPermission();
        $permission2->setKey(AdminPermission::KEY_OFFICIAL_PLATFORM_SALES_INVOICE_CONFIRM);
        $permission2->setName('秒租发票确认权限');
        $permission2->setPlatform(AdminPermission::PERMISSION_PLATFORM_OFFICIAL);
        $permission2->setLevel(AdminPermission::PERMISSION_LEVEL_GLOBAL);
        $permission2->setOpLevelSelect('1,2');
        $permission2->setMaxOpLevel('2');
        $em->persist($permission2);

        $permission3 = new AdminPermission();
        $permission3->setKey(AdminPermission::KEY_OFFICIAL_PLATFORM_TRANSFER_CONFIRM);
        $permission3->setName('线下汇款审核权限');
        $permission3->setPlatform(AdminPermission::PERMISSION_PLATFORM_OFFICIAL);
        $permission3->setLevel(AdminPermission::PERMISSION_LEVEL_GLOBAL);
        $permission3->setOpLevelSelect('1,2');
        $permission3->setMaxOpLevel('2');
        $em->persist($permission3);

        $em->flush();

        $map2 = new AdminPermissionGroupMap();
        $map2->setGroup($officialFinanceGroup);
        $map2->setPermission($officialWithdrawalPermission);
        $em->persist($map2);

        $map4 = new AdminPermissionGroupMap();
        $map4->setGroup($officialFinanceGroup);
        $map4->setPermission($permission1);
        $em->persist($map4);

        $map5 = new AdminPermissionGroupMap();
        $map5->setGroup($officialFinanceGroup);
        $map5->setPermission($permission2);
        $em->persist($map5);

        $map6 = new AdminPermissionGroupMap();
        $map6->setGroup($officialFinanceGroup);
        $map6->setPermission($permission3);
        $em->persist($map6);

        $map7 = new AdminPermissionGroupMap();
        $map7->setGroup($officialFinanceGroup);
        $map7->setPermission($officialInvoicePermission);
        $em->persist($map7);

        $map8 = new AdminPermissionGroupMap();
        $map8->setGroup($officialFinanceGroup);
        $map8->setPermission($officialRefundPermission);
        $em->persist($map8);

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
