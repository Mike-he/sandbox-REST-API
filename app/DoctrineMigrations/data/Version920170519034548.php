<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Sandbox\ApiBundle\Entity\Room\RoomTypeTags;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920170519034548 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
    }

    /**
     * @param Schema $schema
     */
    public function postUp(Schema $schema)
    {
        parent::postUp($schema);

        $em = $this->container->get('doctrine.orm.entity_manager');

        $roomTypes = $em->getRepository('SandboxApiBundle:Room\RoomTypes')->findAll();
        foreach ($roomTypes as $roomType) {
            $type = $roomType->getName();

            switch ($type) {
                case 'office':
                    $typeTag1 = new RoomTypeTags();
                    $typeTag1->setTagKey('manager_office');
                    $typeTag1->setParentType($roomType);
                    $em->persist($typeTag1);

                    $typeTag2 = new RoomTypeTags();
                    $typeTag2->setTagKey('team_office');
                    $typeTag2->setParentType($roomType);
                    $em->persist($typeTag2);

                    $typeTag3 = new RoomTypeTags();
                    $typeTag3->setTagKey('office_suite');
                    $typeTag3->setParentType($roomType);
                    $em->persist($typeTag3);

                    $typeTag4 = new RoomTypeTags();
                    $typeTag4->setTagKey('open_office');
                    $typeTag4->setParentType($roomType);
                    $em->persist($typeTag4);
                    break;
                case 'meeting':
                    $typeTag1 = new RoomTypeTags();
                    $typeTag1->setTagKey('boardroom');
                    $typeTag1->setParentType($roomType);
                    $em->persist($typeTag1);

                    $typeTag2 = new RoomTypeTags();
                    $typeTag2->setTagKey('u_shape');
                    $typeTag2->setParentType($roomType);
                    $em->persist($typeTag2);

                    $typeTag3 = new RoomTypeTags();
                    $typeTag3->setTagKey('classroom');
                    $typeTag3->setParentType($roomType);
                    $em->persist($typeTag3);

                    $typeTag4 = new RoomTypeTags();
                    $typeTag4->setTagKey('open_meeting_space');
                    $typeTag4->setParentType($roomType);
                    $em->persist($typeTag4);
                    break;
                case 'desk':
                    $typeTag1 = new RoomTypeTags();
                    $typeTag1->setTagKey('hot_desk');
                    $typeTag1->setParentType($roomType);
                    $em->persist($typeTag1);

                    $typeTag2 = new RoomTypeTags();
                    $typeTag2->setTagKey('dedicated_desk');
                    $typeTag2->setParentType($roomType);
                    $em->persist($typeTag2);
                    break;
                case 'others':
                    $typeTag1 = new RoomTypeTags();
                    $typeTag1->setTagKey('multi_function_room');
                    $typeTag1->setParentType($roomType);
                    $em->persist($typeTag1);

                    $typeTag2 = new RoomTypeTags();
                    $typeTag2->setTagKey('dinning_hall');
                    $typeTag2->setParentType($roomType);
                    $em->persist($typeTag2);

                    $typeTag3 = new RoomTypeTags();
                    $typeTag3->setTagKey('lecture_hall');
                    $typeTag3->setParentType($roomType);
                    $em->persist($typeTag3);

                    $typeTag4 = new RoomTypeTags();
                    $typeTag4->setTagKey('recording_studio');
                    $typeTag4->setParentType($roomType);
                    $em->persist($typeTag4);

                    $typeTag5 = new RoomTypeTags();
                    $typeTag5->setTagKey('broadcasting_studio');
                    $typeTag5->setParentType($roomType);
                    $em->persist($typeTag5);

                    $typeTag6 = new RoomTypeTags();
                    $typeTag6->setTagKey('photo_studio');
                    $typeTag6->setParentType($roomType);
                    $em->persist($typeTag6);

                    $typeTag7 = new RoomTypeTags();
                    $typeTag7->setTagKey('theater');
                    $typeTag7->setParentType($roomType);
                    $em->persist($typeTag7);

                    $typeTag8 = new RoomTypeTags();
                    $typeTag8->setTagKey('outdoor_space');
                    $typeTag8->setParentType($roomType);
                    $em->persist($typeTag8);

                    $typeTag9 = new RoomTypeTags();
                    $typeTag9->setTagKey('exhibition_hall');
                    $typeTag9->setParentType($roomType);
                    $em->persist($typeTag9);

                    $typeTag10 = new RoomTypeTags();
                    $typeTag10->setTagKey('entertainment_space');
                    $typeTag10->setParentType($roomType);
                    $em->persist($typeTag10);
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
