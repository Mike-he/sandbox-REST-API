<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160920100919 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        // TODO need add position icon first
//        $this->addSql("INSERT INTO admin_position(`name`,`parentPositionId`,`platform`,`salesCompanyId`,`isHidden`,`isSuperAdmin`,`iconId`,`creationDate`,`modificationDate`)
//                        VALUES('SuperAdministrator',null,'official',null,false,true,1,'2016-9-20','2016-9-20');");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
