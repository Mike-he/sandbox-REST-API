<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Sandbox\ApiBundle\Entity\Service\ServiceTypes;
use Sandbox\ApiBundle\Entity\User\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920171227132517 extends AbstractMigration implements ContainerAwareInterface
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

        $type1 = new ServiceTypes();
        $type1->setName("创业指导");
        $type1->setKey(ServiceTypes::TYPE_NAME_STRATING_BUSSINESS);

        $type2 = new ServiceTypes();
        $type2->setName("法律咨询");
        $type2->setKey(ServiceTypes::TYPE_NAME_LAGAL_ADVICE);

        $type3 = new ServiceTypes();
        $type3->setName("财务代收");
        $type3->setKey(ServiceTypes::TYPE_NAME_FINANCIAL_COLLECTION);

        $type4 = new ServiceTypes();
        $type4->setName("其他");
        $type4->setKey(ServiceTypes::TYPE_NAME_OTHER);

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
