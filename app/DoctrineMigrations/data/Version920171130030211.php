<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Sandbox\ApiBundle\Entity\Parameter\Parameter;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920171130030211 extends AbstractMigration implements ContainerAwareInterface
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

        $parameter = new Parameter();
        $parameter->setKey(Parameter::KEY_COMMNUE_EVENT_HOT);
        $parameter->setValue(3);
        $em->persist($parameter);

        $parameter1 = new Parameter();
        $parameter1->setKey(Parameter::KEY_COMMNUE_BUILDING_HOT);
        $parameter1->setValue(3);
        $em->persist($parameter1);

        $parameter2 = new Parameter();
        $parameter2->setKey(Parameter::KEY_COMMNUE_BANNER);
        $parameter2->setValue(5);
        $em->persist($parameter2);

        $parameter3 = new Parameter();
        $parameter3->setKey(Parameter::KEY_COMMNUE_ADVERTISING_MIDDLE);
        $parameter3->setValue(5);
        $em->persist($parameter3);

        $parameter4 = new Parameter();
        $parameter4->setKey(Parameter::KEY_COMMNUE_ADVERTISING_MICRO);
        $parameter4->setValue(5);
        $em->persist($parameter4);

        $parameter5 = new Parameter();
        $parameter5->setKey(Parameter::KEY_COMMNUE_ADVERTISING_SCREEN);
        $parameter5->setValue(5);
        $em->persist($parameter5);

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
