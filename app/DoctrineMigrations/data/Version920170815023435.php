<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionGroupMap;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920170815023435 extends AbstractMigration implements ContainerAwareInterface
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

        $cashierPermission = new AdminPermission();
        $cashierPermission->setKey('sales.platform.cashier');
        $cashierPermission->setName('收银台管理');
        $cashierPermission->setPlatform('sales');
        $cashierPermission->setLevel('global');
        $cashierPermission->setOpLevelSelect('1,2');
        $cashierPermission->setMaxOpLevel('2');
        $em->persist($cashierPermission);

        $financeGroup = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => 'finance',
                'platform' => 'sales',
            ));

        $map = new AdminPermissionGroupMap();
        $map->setPermission($cashierPermission);
        $map->setGroup($financeGroup);
        $em->persist($map);

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
