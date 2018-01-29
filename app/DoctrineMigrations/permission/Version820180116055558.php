<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version820180116055558 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs

    }

    /**
     * @param Schema $schema
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function postUp(Schema $schema)
    {
        parent::postUp($schema);

        /** @var EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');

        $permissionGroup = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy([
                'groupKey' => 'commnue_dashboard',
                'platform' => 'commnue',
            ]);

        $permission = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(['key' => 'commnue.platform.dashboard']);

        $em->remove($permissionGroup);
        $em->remove($permission);

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
