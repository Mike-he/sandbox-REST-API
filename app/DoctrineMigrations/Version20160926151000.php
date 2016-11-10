<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160926151000 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE admin_position ADD sortTime VARCHAR(15) NOT NULL AFTER `iconId`');

        $this->addSql("INSERT INTO admin_position(`name`,`parentPositionId`,`platform`,`salesCompanyId`,`isHidden`,`isSuperAdmin`,`sortTime`,`iconId`,`creationDate`,`modificationDate`)
                        VALUES('超级管理员',null,'official',null,false,true,'1476153473566',1,'2016-9-20','2016-9-20');");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE admin_position DROP sortTime');
    }
}
