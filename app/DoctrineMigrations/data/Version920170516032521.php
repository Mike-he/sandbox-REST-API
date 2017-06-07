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
                    $roomTypeUnit = new RoomTypeUnit();
                    $roomTypeUnit->setUnit('month');
                    $roomTypeUnit->setType($roomType);
                    $em->persist($roomTypeUnit);
                    break;
                case 'meeting':
                    $roomTypeUnit = new RoomTypeUnit();
                    $roomTypeUnit->setUnit('hour');
                    $roomTypeUnit->setType($roomType);
                    $em->persist($roomTypeUnit);

                    $roomTypeUnit2 = new RoomTypeUnit();
                    $roomTypeUnit2->setUnit('day');
                    $roomTypeUnit2->setType($roomType);
                    $em->persist($roomTypeUnit2);
                    break;
                case 'fixed':
                    $roomType->setName('desk');

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
                    break;
                case 'flexible':
                    $em->remove($roomType);
                    break;
                case 'studio':
                    $roomType->setName('others');

                    $roomTypeUnit = new RoomTypeUnit();
                    $roomTypeUnit->setUnit('hour');
                    $roomTypeUnit->setType($roomType);
                    $em->persist($roomTypeUnit);

                    $roomTypeUnit2 = new RoomTypeUnit();
                    $roomTypeUnit2->setUnit('day');
                    $roomTypeUnit2->setType($roomType);
                    $em->persist($roomTypeUnit2);
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
