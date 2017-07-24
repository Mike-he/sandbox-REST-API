<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170719094539 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE enterprise_customer (id INT AUTO_INCREMENT NOT NULL, company_id INT NOT NULL, name VARCHAR(512) NOT NULL, register_address VARCHAR(512) DEFAULT NULL, business_license_number VARCHAR(512) DEFAULT NULL, organization_certificate_code VARCHAR(512) DEFAULT NULL, tax_registration_number VARCHAR(512) DEFAULT NULL, taxpayer_identification_number VARCHAR(512) DEFAULT NULL, bank_name VARCHAR(512) DEFAULT NULL, bank_account_number VARCHAR(512) DEFAULT NULL, website VARCHAR(512) DEFAULT NULL, phone VARCHAR(512) DEFAULT NULL, industry VARCHAR(512) DEFAULT NULL, mailing_address VARCHAR(512) DEFAULT NULL, comment LONGTEXT DEFAULT NULL, creation_date DATETIME NOT NULL, modification_date DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE enterprise_customer_contacts (id INT AUTO_INCREMENT NOT NULL, enterprise_customer_id INT NOT NULL, customer_id INT NOT NULL, contact_position VARCHAR(255) DEFAULT NULL, creation_date DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE enterprise_customer');
        $this->addSql('DROP TABLE enterprise_customer_contacts');
    }
}
