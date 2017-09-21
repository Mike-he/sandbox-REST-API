<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920170523052851 extends AbstractMigration implements ContainerAwareInterface
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

        $chinaZone = '(UTC+08:00)北京，重庆，香港特别行政区，乌鲁木齐';

        $city1 = $em->getRepository('SandboxApiBundle:Room\RoomCity')
            ->findOneBy(['enName' => 'Beijing']);
        if (!is_null($city1)) {
            $city1->setTimezone($chinaZone);
        }

        $city2 = $em->getRepository('SandboxApiBundle:Room\RoomCity')
            ->findOneBy(['enName' => 'Shanghai']);
        if (!is_null($city2)) {
            $city2->setTimezone($chinaZone);
        }

        $city3 = $em->getRepository('SandboxApiBundle:Room\RoomCity')
            ->findOneBy(['enName' => 'Guangzhou']);
        if (!is_null($city3)) {
            $city3->setTimezone($chinaZone);
        }

        $city4 = $em->getRepository('SandboxApiBundle:Room\RoomCity')
            ->findOneBy(['enName' => 'Shenzhen']);
        if (!is_null($city4)) {
            $city4->setTimezone($chinaZone);
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
