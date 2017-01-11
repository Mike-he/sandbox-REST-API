<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170104165205 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sales_company ADD contacter_phone VARCHAR(64) NOT NULL, CHANGE applicantname contacter VARCHAR(64) NOT NULL, CHANGE email contacter_email VARCHAR(255) NOT NULL, CHANGE creationDate creation_date DATETIME NOT NULL, CHANGE modificationDate modification_date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE sales_company_service_infos CHANGE collection_method collection_method VARCHAR(30) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sales_company ADD applicantName VARCHAR(64) NOT NULL, ADD creationDate DATETIME NOT NULL, ADD modificationDate DATETIME NOT NULL, DROP contacter, DROP contacter_phone, CHANGE contacter_email email VARCHAR(255) NOT NULL, DROP creation_date, DROP modification_date');
        $this->addSql('ALTER TABLE sales_company_service_infos CHANGE collection_method collection_method VARCHAR(30) NOT NULL');
    }
}
