<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Sandbox\ApiBundle\Entity\Admin\AdminExcludePermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionGroups;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version820170411075227 extends AbstractMigration implements ContainerAwareInterface
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

        $salesMembershipGroup = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => AdminPermissionGroups::GROUP_KEY_MEMBERSHIP_CARD,
                'platform' => AdminPermissionGroups::GROUP_PLATFORM_SALES,
            ));

        $salesCompanies = $em->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
            ->findAll();

        foreach ($salesCompanies as $company) {
            $excludePermission = new AdminExcludePermission();
            $excludePermission->setSalesCompany($company);
            $excludePermission->setGroup($salesMembershipGroup);
            $excludePermission->setPlatform(AdminPermission::PERMISSION_PLATFORM_SALES);
            $em->persist($excludePermission);
        }

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
