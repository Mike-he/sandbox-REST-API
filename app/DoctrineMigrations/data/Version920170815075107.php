<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionGroupMap;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionGroups;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920170815075107 extends AbstractMigration implements ContainerAwareInterface
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

        $settingGroup = new AdminPermissionGroups();
        $settingGroup->setGroupKey('setting');
        $settingGroup->setPlatform('sales');
        $settingGroup->setGroupName('设置');
        $em->persist($settingGroup);

        $financeGroup = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => 'finance',
                'platform' => 'sales',
            ));

        $enterpriseAccountPermission = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => 'sales.platform.account',
            ));

        $map = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroupMap')
            ->findOneBy(array(
                'permission' => $enterpriseAccountPermission,
                'group' => $financeGroup,
            ));
        if ($map) {
            $em->remove($map);
        }

        $map2 = new AdminPermissionGroupMap();
        $map2->setGroup($settingGroup);
        $map2->setPermission($enterpriseAccountPermission);
        $em->persist($map2);

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
