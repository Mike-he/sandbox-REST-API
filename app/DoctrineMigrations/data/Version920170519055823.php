<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920170519055823 extends AbstractMigration implements ContainerAwareInterface
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

        $rooms = $em->getRepository('SandboxApiBundle:Room\Room')->findAll();
        foreach ($rooms as $room) {
            $typeKey = $room->getType();

            switch ($typeKey) {
                case 'meeting':
                    $room->setTypeTag('boardroom');
                    break;
                case 'office':
                    $room->setTypeTag('team_office');
                    break;
                case 'fixed':
                    $room->setType('desk');
                    $room->setTypeTag('dedicated_desk');
                    break;
                case 'flexible':
                    $room->setType('desk');
                    $room->setTypeTag('hot_desk');
                    break;
                case 'studio':
                    $room->setType('others');
                    $room->setTypeTag('multi_function_room');
                    break;
                case 'space':
                    $room->setType('others');
                    $room->setTypeTag('multi_function_room');
                    break;
                case 'longterm':
                    $room->setType('office');
                    $room->setTypeTag('boardroom');
                    break;
            }

            $em->flush();
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
