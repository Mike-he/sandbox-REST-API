<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Sandbox\ApiBundle\Entity\GenericList\GenericList;
use Sandbox\ApiBundle\Entity\Service\ViewCounts;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920180118155657 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }

    public function postUp(Schema $schema)
    {
        parent::postUp($schema);

        /** @var EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');

        $buildings = $em->getRepository('SandboxApiBundle:Room\RoomBuilding')
            ->findAll();

        foreach ($buildings as $building) {
            $viewCounts = new ViewCounts();
            $viewCounts->setObject(ViewCounts::OBJECT_BUILDING);
            $viewCounts->setObjectId($building->getId());
            $viewCounts->setCount(0);
            $viewCounts->setType(ViewCounts::TYPE_VIEW);

            $em->persist($viewCounts);
        }

        $events = $em->getRepository('SandboxApiBundle:Event\Event')
            ->findAll();

        foreach ($events as $event) {
            $eventId = $event->getId();

            $viewCounts1 = new ViewCounts();
            $viewCounts1->setObject(ViewCounts::OBJECT_EVENT);
            $viewCounts1->setObjectId($eventId);
            $viewCounts1->setCount(0);
            $viewCounts1->setType(ViewCounts::TYPE_VIEW);

            $em->persist($viewCounts1);

            $registerings = $em->getRepository('SandboxApiBundle:Event\EventRegistration')
                ->findBy([
                    'eventId' => $eventId
                ]);
            $counts = count($registerings);
            $viewCounts2 = new ViewCounts();
            $viewCounts2->setObject(ViewCounts::OBJECT_EVENT);
            $viewCounts2->setObjectId($eventId);
            $viewCounts2->setCount($counts);
            $viewCounts2->setType(ViewCounts::TYPE_REGISTERING);

            $em->persist($viewCounts2);
        }

        $em->flush();
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
