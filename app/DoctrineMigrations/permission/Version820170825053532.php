<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionGroupMap;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionGroups;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version820170825053532 extends AbstractMigration implements ContainerAwareInterface
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

        $beanPermission = new AdminPermission();
        $beanPermission->setKey('platform.bean');
        $beanPermission->setName('赤豆管理权限');
        $beanPermission->setPlatform('official');
        $beanPermission->setLevel('global');
        $beanPermission->setOpLevelSelect('1');
        $beanPermission->setMaxOpLevel('1');
        $em->persist($beanPermission);

        $beanGroup = new AdminPermissionGroups();
        $beanGroup->setGroupKey('bean');
        $beanGroup->setGroupName('赤豆管理');
        $beanGroup->setPlatform('official');
        $em->persist($beanGroup);

        $map = new AdminPermissionGroupMap();
        $map->setGroup($beanGroup);
        $map->setPermission($beanPermission);
        $em->persist($map);

        $invoicePermission = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(['key' => 'sales.platform.invoice']);
        $invoicePermission->setName('客户开票权限');

        $serviceBillPermission = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(['key' => 'sales.platform.long_term_service_bills']);
        $em->remove($serviceBillPermission);

        $monthlyBillPermission = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(['key' => 'sales.platform.monthly_bills']);
        $em->remove($monthlyBillPermission);

        $financeSummaryPermission = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(['key' => 'sales.platform.financial_summary']);
        $financeSummaryPermission->setOpLevelSelect('1');
        $financeSummaryPermission->setMaxOpLevel('1');

        $auditPermission = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(['key' => 'sales.platform.audit']);
        $em->remove($auditPermission);

        $withdrawalPermission = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(['key' => 'sales.platform.withdrawal']);
        $withdrawalPermission->setName('账户钱包权限');

        $financeGroup = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy([
                'groupKey' => 'finance',
                'platform' => 'sales',
            ]);

        $requestInvoicePermission = new AdminPermission();
        $requestInvoicePermission->setKey('sales.platform.request_invoice');
        $requestInvoicePermission->setName('索取发票权限');
        $requestInvoicePermission->setPlatform('sales');
        $requestInvoicePermission->setLevel('global');
        $requestInvoicePermission->setOpLevelSelect('1,2');
        $requestInvoicePermission->setMaxOpLevel('2');
        $em->persist($requestInvoicePermission);

        $downloadPermission = new AdminPermission();
        $downloadPermission->setKey('sales.platform.report_download');
        $downloadPermission->setName('报表下载权限');
        $downloadPermission->setPlatform('sales');
        $downloadPermission->setLevel('global');
        $downloadPermission->setOpLevelSelect('1,2');
        $downloadPermission->setMaxOpLevel('2');
        $em->persist($downloadPermission);

        $map2 = new AdminPermissionGroupMap();
        $map2->setGroup($financeGroup);
        $map2->setPermission($requestInvoicePermission);
        $em->persist($map2);

        $map3 = new AdminPermissionGroupMap();
        $map3->setGroup($financeGroup);
        $map3->setPermission($downloadPermission);
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
