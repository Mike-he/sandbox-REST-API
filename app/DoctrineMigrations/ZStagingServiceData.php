<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompanyServiceInfos;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class ZStagingServiceData extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    //Version20170111090021
    
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
    }

    public function postUp(Schema $schema)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        $companies = $em->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')->findAll();
        
        $types = $em->getRepository('SandboxApiBundle:Room\RoomTypes')->findAll();

        foreach ($companies as $company) {
            foreach ($types as $type) {
                $service = new SalesCompanyServiceInfos();

                $service->setRoomTypes($type->getName());
                $service->setCompany($company);
                $service->setServiceFee(0);
                $service->setStatus(true);

                $em->persist($service);
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
