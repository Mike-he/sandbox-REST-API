<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920170519013202 extends AbstractMigration implements ContainerAwareInterface
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

        $roomTypes = $em->getRepository('SandboxApiBundle:Room\RoomTypes')->findAll();
        foreach ($roomTypes as $roomType) {
            $type = $roomType->getName();

            switch ($type) {
                case 'office':
                    $roomType->setIcon('/icon/room_type_office.png');
                    $roomType->setHomepageIcon('/icon/room_type_office_homepage.png');
                    break;
                case 'meeting':
                    $roomType->setIcon('/icon/room_type_meeting.png');
                    $roomType->setHomepageIcon('/icon/room_type_meeting_homepage.png');
                    break;
                case 'desk':
                    $roomType->setIcon('/icon/room_type_fixed.png');
                    $roomType->setHomepageIcon('/icon/room_type_fixed_homepage.png');
                    break;
                case 'others':
                    $roomType->setIcon('/icon/room_type_space.png');
                    $roomType->setHomepageIcon('/icon/room_type_space_homepage.png');
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
