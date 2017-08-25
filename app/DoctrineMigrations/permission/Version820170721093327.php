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
class Version820170721093327 extends AbstractMigration implements ContainerAwareInterface
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

        $tradeGroup = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => 'trade',
                'platform' => 'sales',
            ));
        $tradeGroup->setGroupName('秒租订单');

        $leasePermission = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SALES_BUILDING_LONG_TERM_LEASE,
            ));
        $leaseApplyPermission = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_SALES_BUILDING_LONG_TERM_APPOINTMENT,
            ));

        $map1 = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroupMap')
            ->findOneBy(array(
                'permission' => $leasePermission,
                'group' => $tradeGroup,
            ));
        $map2 = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroupMap')
            ->findOneBy(array(
                'permission' => $leaseApplyPermission,
                'group' => $tradeGroup,
            ));

        if ($map1) {
            $em->remove($map1);
        }

        if ($map2) {
            $em->remove($map2);
        }

        $leaseGroup = new AdminPermissionGroups();
        $leaseGroup->setGroupKey('lease');
        $leaseGroup->setGroupName('合同租赁');
        $leaseGroup->setPlatform('sales');
        $em->persist($leaseGroup);

        $leaseCluePermission = new AdminPermission();
        $leaseCluePermission->setKey('sales.building.lease_clue');
        $leaseCluePermission->setName('线索权限');
        $leaseCluePermission->setPlatform('sales');
        $leaseCluePermission->setLevel('specify');
        $leaseCluePermission->setOpLevelSelect('1,2');
        $leaseCluePermission->setMaxOpLevel('2');
        $em->persist($leaseCluePermission);

        $leaseOfferPermission = new AdminPermission();
        $leaseOfferPermission->setKey('sales.building.lease_offer');
        $leaseOfferPermission->setName('报价权限');
        $leaseOfferPermission->setPlatform('sales');
        $leaseOfferPermission->setLevel('global');
        $leaseOfferPermission->setOpLevelSelect('1,2');
        $leaseOfferPermission->setMaxOpLevel('2');
        $em->persist($leaseOfferPermission);

        $leaseBillPermission = new AdminPermission();
        $leaseBillPermission->setKey('sales.building.bill');
        $leaseBillPermission->setName('账单权限');
        $leaseBillPermission->setPlatform('sales');
        $leaseBillPermission->setLevel('global');
        $leaseBillPermission->setOpLevelSelect('1,2');
        $leaseBillPermission->setMaxOpLevel('2');
        $em->persist($leaseBillPermission);

        $map31 = new AdminPermissionGroupMap();
        $map31->setGroup($leaseGroup);
        $map31->setPermission($leasePermission);
        $em->persist($map31);

        $map3 = new AdminPermissionGroupMap();
        $map3->setGroup($leaseGroup);
        $map3->setPermission($leaseCluePermission);
        $em->persist($map3);

        $map4 = new AdminPermissionGroupMap();
        $map4->setGroup($leaseGroup);
        $map4->setPermission($leaseOfferPermission);
        $em->persist($map4);

        $map5 = new AdminPermissionGroupMap();
        $map5->setGroup($leaseGroup);
        $map5->setPermission($leaseBillPermission);
        $em->persist($map5);

        $spaceGroup = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => 'space',
                'platform' => 'sales',
            ));
        $spaceGroup->setGroupName('空间设置');

        $customerGroup = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => 'user',
                'platform' => 'sales',
            ));
        $customerGroup->setGroupName('客户关系');

        $userPermission = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => 'sales.building.user',
            ));
        $userPermission->setName('客户权限');
        $userPermission->setKey('sales.platform.customer');

        $enterpriseCustomerPermission = new AdminPermission();
        $enterpriseCustomerPermission->setKey('sales.platform.enterprise_customer');
        $enterpriseCustomerPermission->setName('企业账户权限');
        $enterpriseCustomerPermission->setPlatform('sales');
        $enterpriseCustomerPermission->setLevel('global');
        $enterpriseCustomerPermission->setOpLevelSelect('1,2');
        $enterpriseCustomerPermission->setMaxOpLevel('2');
        $em->persist($enterpriseCustomerPermission);

        $userGroupPermission = new AdminPermission();
        $userGroupPermission->setKey('sales.platform.user_group');
        $userGroupPermission->setName('用户组权限');
        $userGroupPermission->setPlatform('sales');
        $userGroupPermission->setLevel('global');
        $userGroupPermission->setOpLevelSelect('1,2');
        $userGroupPermission->setMaxOpLevel('2');
        $em->persist($userGroupPermission);

        $map6 = new AdminPermissionGroupMap();
        $map6->setGroup($customerGroup);
        $map6->setPermission($enterpriseCustomerPermission);
        $em->persist($map6);

        $map7 = new AdminPermissionGroupMap();
        $map7->setGroup($customerGroup);
        $map7->setPermission($userGroupPermission);
        $em->persist($map7);

        $adminGroup = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => 'admin',
                'platform' => 'sales',
            ));
        $adminGroup->setGroupName('管理员');

        $membershipGroup = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => 'membership',
                'platform' => 'sales',
            ));
        $membershipGroup->setGroupName('会员卡设置');

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
