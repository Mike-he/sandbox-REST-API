<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Sandbox\ApiBundle\Entity\Product\ProductLeasingSet;
use Sandbox\ApiBundle\Entity\Property\PropertyTypes;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920170515020302 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        parent::postUp($schema);

        $em = $this->container->get('doctrine.orm.entity_manager');

        $preOrderModule = $em->getRepository('SandboxApiBundle:Log\LogModules')
            ->findOneBy(array(
                'name' => 'room_order_preorder',
            ));
        if (!is_null($preOrderModule)) {
            $preOrderModule->setDescription('推送空间订单');
        }

        $reserveModule = $em->getRepository('SandboxApiBundle:Log\LogModules')
            ->findOneBy(array(
                'name' => 'room_order_reserve',
            ));
        if (!is_null($reserveModule)) {
            $reserveModule->setDescription('设置内部占用');
        }

        $type1 = new PropertyTypes();
        $type1->setName('hotel');
        $type1->setCommunityIcon('/icon/community_property_hotel');
        $type1->setApplicationIcon('/icon/application_property_hotel');

        $type2 = new PropertyTypes();
        $type2->setName('incubator');
        $type2->setCommunityIcon('/icon/community_property_incubator');
        $type2->setApplicationIcon('/icon/application_property_incubator');

        $type3 = new PropertyTypes();
        $type3->setName('commercial_center');
        $type3->setCommunityIcon('/icon/community_property_commercial_center');
        $type3->setApplicationIcon('/icon/application_property_commercial_center');

        $type4 = new PropertyTypes();
        $type4->setName('joint_workspace');
        $type4->setCommunityIcon('/icon/community_property_joint_workspace');
        $type4->setApplicationIcon('/icon/application_property_joint_workspace');

        $em->persist($type1);
        $em->persist($type2);
        $em->persist($type3);
        $em->persist($type4);

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
