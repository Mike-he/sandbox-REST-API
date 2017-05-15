<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Sandbox\ApiBundle\Entity\Room\RoomTypesGroups;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920170512053932 extends AbstractMigration implements ContainerAwareInterface
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

        $roomTypesGroups1 = new RoomTypesGroups();
        $roomTypesGroups1->setGroupKey(RoomTypesGroups::KEY_MEETING);
        $roomTypesGroups1->setIcon('/icon/room_type_meeting.png');

        $roomTypesGroups2 = new RoomTypesGroups();
        $roomTypesGroups2->setGroupKey(RoomTypesGroups::KEY_DESK);
        $roomTypesGroups2->setIcon('/icon/room_type_fixed.png');

        $roomTypesGroups3 = new RoomTypesGroups();
        $roomTypesGroups3->setGroupKey(RoomTypesGroups::KEY_OFFICE);
        $roomTypesGroups3->setIcon('/icon/room_type_office.png');

        $roomTypesGroups4 = new RoomTypesGroups();
        $roomTypesGroups4->setGroupKey(RoomTypesGroups::KEY_OTHERS);
        $roomTypesGroups4->setIcon('/icon/room_type_space.png');

        $em->persist($roomTypesGroups1);
        $em->persist($roomTypesGroups2);
        $em->persist($roomTypesGroups3);
        $em->persist($roomTypesGroups4);

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
