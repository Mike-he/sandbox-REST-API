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
class Version20170103030413 extends AbstractMigration implements ContainerAwareInterface
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

        $salesInvoiceGroup = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => 'invoice',
                'platform' => 'sales',
            ));
        $em->remove($salesInvoiceGroup);

        $salesFinanceGroup = new AdminPermissionGroups();
        $salesFinanceGroup->setGroupKey('finance');
        $salesFinanceGroup->setGroupName('财务模块');
        $salesFinanceGroup->setPlatform('sales');

        $salesInvoicePermission = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SALES_PLATFORM_INVOICE,
            ));

        $salesPermission1 = new AdminPermission();
        $salesPermission1->setKey(AdminPermission::KEY_SALES_PLATFORM_LONG_TERM_SERVICE_BILLS);
        $salesPermission1->setName('长租服务费账单权限');
        $salesPermission1->setPlatform('sales');
        $salesPermission1->setLevel('global');
        $salesPermission1->setOpLevelSelect('1,2');
        $salesPermission1->setMaxOpLevel('2');

        $salesPermission2 = new AdminPermission();
        $salesPermission2->setKey(AdminPermission::KEY_SALES_PLATFORM_MONTHLY_BILLS);
        $salesPermission2->setName('秒租月结账单权限');
        $salesPermission2->setPlatform('sales');
        $salesPermission2->setLevel('global');
        $salesPermission2->setOpLevelSelect('1,2');
        $salesPermission2->setMaxOpLevel('2');

        $salesPermission3 = new AdminPermission();
        $salesPermission3->setKey(AdminPermission::KEY_SALES_PLATFORM_FINANCIAL_SUMMARY);
        $salesPermission3->setName('财务汇总权限');
        $salesPermission3->setPlatform('sales');
        $salesPermission3->setLevel('global');
        $salesPermission3->setOpLevelSelect('1,2');
        $salesPermission3->setMaxOpLevel('2');

        $salesPermission4 = new AdminPermission();
        $salesPermission4->setKey(AdminPermission::KEY_SALES_PLATFORM_WITHDRAWAL);
        $salesPermission4->setName('提现权限');
        $salesPermission4->setPlatform('sales');
        $salesPermission4->setLevel('global');
        $salesPermission4->setOpLevelSelect('1,2');
        $salesPermission4->setMaxOpLevel('2');

        $salesPermission5 = new AdminPermission();
        $salesPermission5->setKey(AdminPermission::KEY_SALES_PLATFORM_AUDIT);
        $salesPermission5->setName('线下支付审核权限');
        $salesPermission5->setPlatform('sales');
        $salesPermission5->setLevel('global');
        $salesPermission5->setOpLevelSelect('1,2');
        $salesPermission5->setMaxOpLevel('2');

        $salesPermission6 = new AdminPermission();
        $salesPermission6->setKey(AdminPermission::KEY_SALES_PLATFORM_ACCOUNT);
        $salesPermission6->setName('企业账户管理权限');
        $salesPermission6->setPlatform('sales');
        $salesPermission6->setLevel('global');
        $salesPermission6->setOpLevelSelect('1,2');
        $salesPermission6->setMaxOpLevel('2');

        $map1 = new AdminPermissionGroupMap();
        $map1->setPermission($salesInvoicePermission);
        $map1->setGroup($salesFinanceGroup);

        $map2 = new AdminPermissionGroupMap();
        $map2->setPermission($salesPermission1);
        $map2->setGroup($salesFinanceGroup);

        $map3 = new AdminPermissionGroupMap();
        $map3->setPermission($salesPermission2);
        $map3->setGroup($salesFinanceGroup);

        $map4 = new AdminPermissionGroupMap();
        $map4->setPermission($salesPermission3);
        $map4->setGroup($salesFinanceGroup);

        $map5 = new AdminPermissionGroupMap();
        $map5->setPermission($salesPermission4);
        $map5->setGroup($salesFinanceGroup);

        $map6 = new AdminPermissionGroupMap();
        $map6->setPermission($salesPermission5);
        $map6->setGroup($salesFinanceGroup);

        $map7 = new AdminPermissionGroupMap();
        $map7->setPermission($salesPermission6);
        $map7->setGroup($salesFinanceGroup);

        $em->persist($salesFinanceGroup);
        $em->persist($salesPermission1);
        $em->persist($salesPermission2);
        $em->persist($salesPermission3);
        $em->persist($salesPermission4);
        $em->persist($salesPermission5);
        $em->persist($salesPermission6);
        $em->persist($map1);
        $em->persist($map2);
        $em->persist($map3);
        $em->persist($map4);
        $em->persist($map5);
        $em->persist($map6);
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
