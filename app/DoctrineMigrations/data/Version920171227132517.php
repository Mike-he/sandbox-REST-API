<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Sandbox\ApiBundle\Entity\Service\ServiceType;
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

        $type1 = new ServiceType();
        $type1->setName(ServiceType::TYPE_NAME_STRATING_BUSSINESS);

        $type2 = new ServiceType();
        $type2->setName(ServiceType::TYPE_NAME_LAGAL_ADVICE);

        $type3 = new ServiceType();
        $type3->setName(ServiceType::TYPE_NAME_FINANCIAL_COLLECTION);

        $type4 = new ServiceType();
        $type4->setName(ServiceType::TYPE_NAME_OTHER);

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
