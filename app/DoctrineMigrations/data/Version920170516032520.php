<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Sandbox\ApiBundle\Entity\Room\RoomTypes;
use Sandbox\ApiBundle\Entity\Room\RoomTypeUnit;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920170516032520 extends AbstractMigration implements ContainerAwareInterface
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
                case RoomTypes::TYPE_NAME_MEETING:
                    $roomTypeUnit = new RoomTypeUnit();
                    $roomTypeUnit->setUnit('day');
                    $roomTypeUnit->setType($roomType);
                    $em->persist($roomTypeUnit);
                    break;
                case RoomTypes::TYPE_NAME_FIXED:
                    $roomTypeUnit = new RoomTypeUnit();
                    $roomTypeUnit->setUnit('day');
                    $roomTypeUnit->setType($roomType);
                    $em->persist($roomTypeUnit);

                    $roomTypeUnit2 = new RoomTypeUnit();
                    $roomTypeUnit2->setUnit('week');
                    $roomTypeUnit2->setType($roomType);
                    $em->persist($roomTypeUnit2);
                    break;
                case RoomTypes::TYPE_NAME_FLEXIBLE:
                    $roomTypeUnit = new RoomTypeUnit();
                    $roomTypeUnit->setUnit('month');
                    $roomTypeUnit->setType($roomType);
                    $em->persist($roomTypeUnit);

                    $roomTypeUnit2 = new RoomTypeUnit();
                    $roomTypeUnit2->setUnit('week');
                    $roomTypeUnit2->setType($roomType);
                    $em->persist($roomTypeUnit2);
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
