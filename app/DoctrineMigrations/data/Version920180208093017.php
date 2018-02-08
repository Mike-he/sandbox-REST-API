<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Sandbox\ApiBundle\Entity\GenericList\GenericList;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920180208093017 extends AbstractMigration implements ContainerAwareInterface
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

        $list1 = new GenericList();
        $list1->setColumn('sales_company_name');
        $list1->setName('活动发起方');
        $list1->setDefault(true);
        $list1->setRequired(false);
        $list1->setObject(GenericList::OBJECT_COMMNUE_ACTIVITY);
        $list1->setPlatform(GenericList::OBJECT_PLATFORM_COMMNUE);

        $list2 = new GenericList();
        $list2->setColumn('sales_company_name');
        $list2->setName('活动发起方');
        $list2->setDefault(true);
        $list2->setRequired(false);
        $list2->setObject(GenericList::OBJECT_ACTIVITY);
        $list2->setPlatform(GenericList::OBJECT_PLATFORM_OFFICIAL);

        $list3 = new GenericList();
        $list3->setColumn('sales_company_name');
        $list3->setName('活动发起方');
        $list3->setDefault(true);
        $list3->setRequired(false);
        $list3->setObject(GenericList::OBJECT_SALES_ACTIVITY);
        $list3->setPlatform(GenericList::OBJECT_PLATFORM_SALES);

        $em->persist($list1);
        $em->persist($list2);
        $em->persist($list3);

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
