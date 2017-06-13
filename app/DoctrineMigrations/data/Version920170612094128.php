<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920170612094128 extends AbstractMigration implements ContainerAwareInterface
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

        $typeTags = $em->getRepository('SandboxApiBundle:Room\RoomTypeTags')
            ->findAll();

        foreach ($typeTags as $tag) {
            switch ($tag->getTagKey()) {
                case 'manager_office':
                    $tag->setIcon('/icon/room_type_tags/manager_office.png');
                    $tag->setIconSelected('/icon/room_type_tags/manager_office_selected.png');
                    break;
                case 'team_office':
                    $tag->setIcon('/icon/room_type_tags/team_office.png');
                    $tag->setIconSelected('/icon/room_type_tags/team_office_selected.png');
                    break;
                case 'office_suite':
                    $tag->setIcon('/icon/room_type_tags/office_suite.png');
                    $tag->setIconSelected('/icon/room_type_tags/office_suite_selected.png');
                    break;
                case 'open_office':
                    $tag->setIcon('/icon/room_type_tags/open_office.png');
                    $tag->setIconSelected('/icon/room_type_tags/open_office_selected.png');
                    break;
                case 'boardroom':
                    $tag->setIcon('/icon/room_type_tags/boardroom.png');
                    $tag->setIconSelected('/icon/room_type_tags/boardroom_selected.png');
                    break;
                case 'u_shape':
                    $tag->setIcon('/icon/room_type_tags/u_shape.png');
                    $tag->setIconSelected('/icon/room_type_tags/u_shape_selected.png');
                    break;
                case 'classroom':
                    $tag->setIcon('/icon/room_type_tags/classroom.png');
                    $tag->setIconSelected('/icon/room_type_tags/classroom_selected.png');
                    break;
                case 'open_meeting_space':
                    $tag->setIcon('/icon/room_type_tags/open_meeting_space.png');
                    $tag->setIconSelected('/icon/room_type_tags/open_meeting_space_selected.png');
                    break;
                case 'hot_desk':
                    $tag->setIcon('/icon/room_type_tags/hot_desk.png');
                    $tag->setIconSelected('/icon/room_type_tags/hot_desk_selected.png');
                    break;
                case 'dedicated_desk':
                    $tag->setIcon('/icon/room_type_tags/dedicated_desk.png');
                    $tag->setIconSelected('/icon/room_type_tags/dedicated_desk_selected.png');
                    break;
                case 'multi_function_room':
                    $tag->setIcon('/icon/room_type_tags/multi_function_room.png');
                    $tag->setIconSelected('/icon/room_type_tags/multi_function_room_selected.png');
                    break;
                case 'dinning_hall':
                    $tag->setIcon('/icon/room_type_tags/dinning_hall.png');
                    $tag->setIconSelected('/icon/room_type_tags/dinning_hall_selected.png');
                    break;
                case 'lecture_hall':
                    $tag->setIcon('/icon/room_type_tags/lecture_hall.png');
                    $tag->setIconSelected('/icon/room_type_tags/lecture_hall_selected.png');
                    break;
                case 'recording_studio':
                    $tag->setIcon('/icon/room_type_tags/recording_studio.png');
                    $tag->setIconSelected('/icon/room_type_tags/recording_studio_selected.png');
                    break;
                case 'broadcasting_studio':
                    $tag->setIcon('/icon/room_type_tags/broadcasting_studio.png');
                    $tag->setIconSelected('/icon/room_type_tags/broadcasting_studio_selected.png');
                    break;
                case 'photo_studio':
                    $tag->setIcon('/icon/room_type_tags/photo_studio.png');
                    $tag->setIconSelected('/icon/room_type_tags/photo_studio_selected.png');
                    break;
                case 'theater':
                    $tag->setIcon('/icon/room_type_tags/theater.png');
                    $tag->setIconSelected('/icon/room_type_tags/theater_selected.png');
                    break;
                case 'outdoor_space':
                    $tag->setIcon('/icon/room_type_tags/outdoor_space.png');
                    $tag->setIconSelected('/icon/room_type_tags/outdoor_space_selected.png');
                    break;
                case 'exhibition_hall':
                    $tag->setIcon('/icon/room_type_tags/exhibition_hall.png');
                    $tag->setIconSelected('/icon/room_type_tags/exhibition_hall_selected.png');
                    break;
                case 'entertainment_space':
                    $tag->setIcon('/icon/room_type_tags/entertainment_space.png');
                    $tag->setIconSelected('/icon/room_type_tags/entertainment_space_selected.png');
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
