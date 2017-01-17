<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170117065043 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE finance_long_rent_bill (id INT AUTO_INCREMENT NOT NULL, amount NUMERIC(10, 2) NOT NULL, status VARCHAR(15) NOT NULL, company_id INT NOT NULL, creation_date DATETIME NOT NULL, modification_date DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE finance_long_rent_service_bill (id INT AUTO_INCREMENT NOT NULL, bill_id INT DEFAULT NULL, serial_number VARCHAR(50) NOT NULL, amount NUMERIC(10, 2) NOT NULL, type VARCHAR(10) NOT NULL, company_id INT NOT NULL, creation_date DATETIME NOT NULL, modification_date DATETIME NOT NULL, INDEX IDX_D004F8171A8C12F5 (bill_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE finance_long_rent_service_bill ADD CONSTRAINT FK_D004F8171A8C12F5 FOREIGN KEY (bill_id) REFERENCES lease_bill (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE finance_long_rent_bill');
        $this->addSql('DROP TABLE finance_long_rent_service_bill');
    }
}
