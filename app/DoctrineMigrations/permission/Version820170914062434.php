<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionGroupMap;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version820170914062434 extends AbstractMigration implements ContainerAwareInterface
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

        $adminGroup = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy([
                'groupKey' => 'admin',
                'platform' => 'sales',
            ]);

        $em->remove($adminGroup);

        $adminPermission = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(['key' => 'sales.platform.admin']);

        $settingGroup = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy([
                'groupKey' => 'setting',
                'platform' => 'sales',
            ]);

        $map = new AdminPermissionGroupMap();
        $map->setGroup($settingGroup);
        $map->setPermission($adminPermission);
        $em->persist($map);

        $spaceGroup = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy([
                'groupKey' => 'space',
                'platform' => 'sales',
            ]);

        $longAppointPermission = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(['key' => 'sales.building.long_term_appointment']);

        $longLeasePermission = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(['key' => 'sales.building.long_term_lease']);

        $map2 = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroupMap')
            ->findOneBy([
                'group' => $spaceGroup,
                'permission' => $longAppointPermission,
            ]);

        $map3 = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroupMap')
            ->findOneBy([
                'group' => $spaceGroup,
                'permission' => $longLeasePermission,
            ]);

        $em->remove($map2);
        $em->remove($map3);

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
