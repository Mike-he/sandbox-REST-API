<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Sandbox\ApiBundle\Entity\Room\RoomTypes;
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

        $roomTypesMeeting = new RoomTypesGroups();
        $roomTypesMeeting->setGroupKey(RoomTypesGroups::KEY_MEETING);
        $roomTypesMeeting->setIcon('/icon/room_type_meeting.png');
        $roomTypesMeeting->setHomepageIcon('/icon/room_type_meeting_homepage.png');

        $roomTypesDesk = new RoomTypesGroups();
        $roomTypesDesk->setGroupKey(RoomTypesGroups::KEY_DESK);
        $roomTypesDesk->setIcon('/icon/room_type_fixed.png');
        $roomTypesDesk->setHomepageIcon('/icon/room_type_fixed_homepage.png');

        $roomTypesOffice = new RoomTypesGroups();
        $roomTypesOffice->setGroupKey(RoomTypesGroups::KEY_OFFICE);
        $roomTypesOffice->setIcon('/icon/room_type_office.png');
        $roomTypesOffice->setHomepageIcon('/icon/room_type_office_homepage.png');

        $roomTypesOthers = new RoomTypesGroups();
        $roomTypesOthers->setGroupKey(RoomTypesGroups::KEY_OTHERS);
        $roomTypesOthers->setIcon('/icon/room_type_space.png');
        $roomTypesOthers->setHomepageIcon('/icon/room_type_space_homepage.png');

        $em->persist($roomTypesMeeting);
        $em->persist($roomTypesDesk);
        $em->persist($roomTypesOffice);
        $em->persist($roomTypesOthers);

        $roomTypes = $em->getRepository('SandboxApiBundle:Room\RoomTypes')->findAll();
        foreach ($roomTypes as $roomType) {
            $type = $roomType->getName();
            switch ($type) {
                case RoomTypes::TYPE_NAME_OFFICE:
                    $roomType->setGroup($roomTypesOffice);
                    break;
                case RoomTypes::TYPE_NAME_MEETING:
                    $roomType->setGroup($roomTypesMeeting);
                    break;
                case RoomTypes::TYPE_NAME_FIXED:
                    $roomType->setGroup($roomTypesDesk);
                    break;
                case RoomTypes::TYPE_NAME_FLEXIBLE:
                    $roomType->setGroup($roomTypesDesk);
                    break;
                case RoomTypes::TYPE_NAME_STUDIO:
                    $roomType->setGroup($roomTypesOthers);
                    break;
                case RoomTypes::TYPE_NAME_SPACE:
                    $roomType->setGroup($roomTypesOthers);
                    break;
                case RoomTypes::TYPE_NAME_LONGTERM:
                    $roomType->setGroup($roomTypesOffice);
                    break;
            }
        }

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
