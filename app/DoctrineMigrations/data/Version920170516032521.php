<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Sandbox\ApiBundle\Entity\Room\RoomTypeTags;
use Sandbox\ApiBundle\Entity\Room\RoomTypeUnit;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920170516032521 extends AbstractMigration implements ContainerAwareInterface
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

        $roomTypeUnits = $em->getRepository('SandboxApiBundle:Room\RoomTypeUnit')->findAll();
        foreach ($roomTypeUnits as $unit) {
            $em->remove($unit);
        }
        $em->flush();

        $roomTypes = $em->getRepository('SandboxApiBundle:Room\RoomTypes')->findAll();
        foreach ($roomTypes as $roomType) {
            $type = $roomType->getName();

            switch ($type) {
                case 'office':
                    $roomType->setIcon('/icon/room_type_office.png');
                    $roomType->setHomepageIcon('/icon/room_type_office_homepage.png');

                    //RoomTypeUnit
                    $roomTypeUnit = new RoomTypeUnit();
                    $roomTypeUnit->setUnit('month');
                    $roomTypeUnit->setType($roomType);
                    $em->persist($roomTypeUnit);

                    //RoomTypeTags
                    $typeTag1 = new RoomTypeTags();
                    $typeTag1->setTagKey('manager_office');
                    $typeTag1->setParentType($roomType);
                    $typeTag1->setIcon('/icon/room_type_tags/manager_office.png');
                    $typeTag1->setIconSelected('/icon/room_type_tags/manager_office_selected.png');
                    $em->persist($typeTag1);

                    $typeTag2 = new RoomTypeTags();
                    $typeTag2->setTagKey('team_office');
                    $typeTag2->setParentType($roomType);
                    $typeTag2->setIcon('/icon/room_type_tags/team_office.png');
                    $typeTag2->setIconSelected('/icon/room_type_tags/team_office_selected.png');
                    $em->persist($typeTag2);

                    $typeTag3 = new RoomTypeTags();
                    $typeTag3->setTagKey('office_suite');
                    $typeTag3->setParentType($roomType);
                    $typeTag3->setIcon('/icon/room_type_tags/office_suite.png');
                    $typeTag3->setIconSelected('/icon/room_type_tags/office_suite_selected.png');
                    $em->persist($typeTag3);

                    $typeTag4 = new RoomTypeTags();
                    $typeTag4->setTagKey('open_office');
                    $typeTag4->setParentType($roomType);
                    $typeTag4->setIcon('/icon/room_type_tags/open_office.png');
                    $typeTag4->setIconSelected('/icon/room_type_tags/open_office_selected.png');
                    $em->persist($typeTag4);
                    break;
                case 'meeting':
                    $roomType->setIcon('/icon/room_type_meeting.png');
                    $roomType->setHomepageIcon('/icon/room_type_meeting_homepage.png');

                    //RoomTypeUnit
                    $roomTypeUnit = new RoomTypeUnit();
                    $roomTypeUnit->setUnit('hour');
                    $roomTypeUnit->setType($roomType);
                    $em->persist($roomTypeUnit);

                    $roomTypeUnit2 = new RoomTypeUnit();
                    $roomTypeUnit2->setUnit('day');
                    $roomTypeUnit2->setType($roomType);
                    $em->persist($roomTypeUnit2);

                    //RoomTypeTags
                    $typeTag1 = new RoomTypeTags();
                    $typeTag1->setTagKey('boardroom');
                    $typeTag1->setParentType($roomType);
                    $typeTag1->setIcon('/icon/room_type_tags/boardroom.png');
                    $typeTag1->setIconSelected('/icon/room_type_tags/boardroom_selected.png');
                    $em->persist($typeTag1);

                    $typeTag2 = new RoomTypeTags();
                    $typeTag2->setTagKey('u_shape');
                    $typeTag2->setParentType($roomType);
                    $typeTag2->setIcon('/icon/room_type_tags/u_shape.png');
                    $typeTag2->setIconSelected('/icon/room_type_tags/u_shape_selected.png');
                    $em->persist($typeTag2);

                    $typeTag3 = new RoomTypeTags();
                    $typeTag3->setTagKey('classroom');
                    $typeTag3->setParentType($roomType);
                    $typeTag3->setIcon('/icon/room_type_tags/classroom.png');
                    $typeTag3->setIconSelected('/icon/room_type_tags/classroom_selected.png');
                    $em->persist($typeTag3);

                    $typeTag4 = new RoomTypeTags();
                    $typeTag4->setTagKey('open_meeting_space');
                    $typeTag4->setParentType($roomType);
                    $typeTag4->setIcon('/icon/room_type_tags/open_meeting_space.png');
                    $typeTag4->setIconSelected('/icon/room_type_tags/open_meeting_space_selected.png');
                    $em->persist($typeTag4);
                    break;
                case 'fixed':
                    $roomType->setName('desk');
                    $roomType->setIcon('/icon/room_type_fixed.png');
                    $roomType->setHomepageIcon('/icon/room_type_fixed_homepage.png');

                    //RoomTypeUnit
                    $roomTypeUnit = new RoomTypeUnit();
                    $roomTypeUnit->setUnit('day');
                    $roomTypeUnit->setType($roomType);
                    $em->persist($roomTypeUnit);

                    $roomTypeUnit2 = new RoomTypeUnit();
                    $roomTypeUnit2->setUnit('week');
                    $roomTypeUnit2->setType($roomType);
                    $em->persist($roomTypeUnit2);

                    $roomTypeUnit3 = new RoomTypeUnit();
                    $roomTypeUnit3->setUnit('month');
                    $roomTypeUnit3->setType($roomType);
                    $em->persist($roomTypeUnit3);

                    //RoomTypeTags
                    $typeTag1 = new RoomTypeTags();
                    $typeTag1->setTagKey('hot_desk');
                    $typeTag1->setParentType($roomType);
                    $typeTag1->setIcon('/icon/room_type_tags/hot_desk.png');
                    $typeTag1->setIconSelected('/icon/room_type_tags/hot_desk_selected.png');
                    $em->persist($typeTag1);

                    $typeTag2 = new RoomTypeTags();
                    $typeTag2->setTagKey('dedicated_desk');
                    $typeTag2->setParentType($roomType);
                    $typeTag2->setIcon('/icon/room_type_tags/dedicated_desk.png');
                    $typeTag2->setIconSelected('/icon/room_type_tags/dedicated_desk_selected.png');
                    $em->persist($typeTag2);
                    break;
                case 'flexible':
                    $em->remove($roomType);
                    break;
                case 'studio':
                    $roomType->setName('others');
                    $roomType->setIcon('/icon/room_type_space.png');
                    $roomType->setHomepageIcon('/icon/room_type_space_homepage.png');

                    //RoomTypeUnit
                    $roomTypeUnit = new RoomTypeUnit();
                    $roomTypeUnit->setUnit('hour');
                    $roomTypeUnit->setType($roomType);
                    $em->persist($roomTypeUnit);

                    $roomTypeUnit2 = new RoomTypeUnit();
                    $roomTypeUnit2->setUnit('day');
                    $roomTypeUnit2->setType($roomType);
                    $em->persist($roomTypeUnit2);

                    //RoomTypeTags
                    $typeTag1 = new RoomTypeTags();
                    $typeTag1->setTagKey('multi_function_room');
                    $typeTag1->setParentType($roomType);
                    $typeTag1->setIcon('/icon/room_type_tags/multi_function_room.png');
                    $typeTag1->setIconSelected('/icon/room_type_tags/multi_function_room_selected.png');
                    $em->persist($typeTag1);

                    $typeTag2 = new RoomTypeTags();
                    $typeTag2->setTagKey('dinning_hall');
                    $typeTag2->setParentType($roomType);
                    $typeTag2->setIcon('/icon/room_type_tags/dinning_hall.png');
                    $typeTag2->setIconSelected('/icon/room_type_tags/dinning_hall_selected.png');
                    $em->persist($typeTag2);

                    $typeTag3 = new RoomTypeTags();
                    $typeTag3->setTagKey('lecture_hall');
                    $typeTag3->setParentType($roomType);
                    $typeTag3->setIcon('/icon/room_type_tags/lecture_hall.png');
                    $typeTag3->setIconSelected('/icon/room_type_tags/lecture_hall_selected.png');
                    $em->persist($typeTag3);

                    $typeTag4 = new RoomTypeTags();
                    $typeTag4->setTagKey('recording_studio');
                    $typeTag4->setParentType($roomType);
                    $typeTag4->setIcon('/icon/room_type_tags/recording_studio.png');
                    $typeTag4->setIconSelected('/icon/room_type_tags/recording_studio_selected.png');
                    $em->persist($typeTag4);

                    $typeTag5 = new RoomTypeTags();
                    $typeTag5->setTagKey('broadcasting_studio');
                    $typeTag5->setParentType($roomType);
                    $typeTag5->setIcon('/icon/room_type_tags/broadcasting_studio.png');
                    $typeTag5->setIconSelected('/icon/room_type_tags/broadcasting_studio_selected.png');
                    $em->persist($typeTag5);

                    $typeTag6 = new RoomTypeTags();
                    $typeTag6->setTagKey('photo_studio');
                    $typeTag6->setParentType($roomType);
                    $typeTag6->setIcon('/icon/room_type_tags/photo_studio.png');
                    $typeTag6->setIconSelected('/icon/room_type_tags/photo_studio_selected.png');
                    $em->persist($typeTag6);

                    $typeTag7 = new RoomTypeTags();
                    $typeTag7->setTagKey('theater');
                    $typeTag7->setParentType($roomType);
                    $typeTag7->setIcon('/icon/room_type_tags/theater.png');
                    $typeTag7->setIconSelected('/icon/room_type_tags/theater_selected.png');
                    $em->persist($typeTag7);

                    $typeTag8 = new RoomTypeTags();
                    $typeTag8->setTagKey('outdoor_space');
                    $typeTag8->setParentType($roomType);
                    $typeTag8->setIcon('/icon/room_type_tags/outdoor_space.png');
                    $typeTag8->setIconSelected('/icon/room_type_tags/outdoor_space_selected.png');
                    $em->persist($typeTag8);

                    $typeTag9 = new RoomTypeTags();
                    $typeTag9->setTagKey('exhibition_hall');
                    $typeTag9->setParentType($roomType);
                    $typeTag9->setIcon('/icon/room_type_tags/exhibition_hall.png');
                    $typeTag9->setIconSelected('/icon/room_type_tags/exhibition_hall_selected.png');
                    $em->persist($typeTag9);

                    $typeTag10 = new RoomTypeTags();
                    $typeTag10->setTagKey('entertainment_space');
                    $typeTag10->setParentType($roomType);
                    $typeTag10->setIcon('/icon/room_type_tags/entertainment_space.png');
                    $typeTag10->setIconSelected('/icon/room_type_tags/entertainment_space_selected.png');
                    $em->persist($typeTag10);
                    break;
                case 'space':
                    $em->remove($roomType);
                    break;
                case 'longterm':
                    $em->remove($roomType);
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
