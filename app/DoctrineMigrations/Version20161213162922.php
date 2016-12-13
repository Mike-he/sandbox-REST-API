<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Sandbox\ApiBundle\Entity\Lease\LeaseRentTypes;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161213162922 extends AbstractMigration implements ContainerAwareInterface
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
        $manager = $this->container->get('doctrine.orm.entity_manager');

        $lrt1 = new LeaseRentTypes();
        $lrt1->setName('水费');
        $lrt1->setNameEn('Water');

        $lrt2 = new LeaseRentTypes();
        $lrt2->setName('电费');
        $lrt2->setNameEn('Electricity');

        $lrt3 = new LeaseRentTypes();
        $lrt3->setName('场地使用费');
        $lrt3->setNameEn('Field use');

        $lrt4 = new LeaseRentTypes();
        $lrt4->setName('场地服务费');
        $lrt4->setNameEn('Field service');

        $lrt5 = new LeaseRentTypes();
        $lrt5->setName('物业管理费');
        $lrt5->setNameEn('Property management');

        $lrt6 = new LeaseRentTypes();
        $lrt6->setName('空调使用费');
        $lrt6->setNameEn('Air conditioning');

        $lrt7 = new LeaseRentTypes();
        $lrt7->setName('增值税税金');
        $lrt7->setNameEn('The VAT tax');

        $lrt8 = new LeaseRentTypes();
        $lrt8->setName('网络通讯费');
        $lrt8->setNameEn('Network');

        $lrt9 = new LeaseRentTypes();
        $lrt9->setName('其他');
        $lrt9->setNameEn('Other');

        $manager->persist($lrt1);
        $manager->persist($lrt2);
        $manager->persist($lrt3);
        $manager->persist($lrt4);
        $manager->persist($lrt5);
        $manager->persist($lrt6);
        $manager->persist($lrt7);
        $manager->persist($lrt8);
        $manager->persist($lrt9);

        $manager->flush();
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
