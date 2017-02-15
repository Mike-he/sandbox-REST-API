<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170111061031 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sales_company_profile_account (id INT AUTO_INCREMENT NOT NULL, sales_company_id INT NOT NULL, sales_company_name VARCHAR(128) NOT NULL, business_scope VARCHAR(1024) NOT NULL, bank_account_name VARCHAR(255) NOT NULL, bank_account_number VARCHAR(255) NOT NULL, creation_date DATETIME NOT NULL, modification_date DATETIME NOT NULL, INDEX IDX_A63DE3DD98A7ADBA (sales_company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sales_company_profile_express (id INT AUTO_INCREMENT NOT NULL, sales_company_id INT NOT NULL, recipient VARCHAR(255) NOT NULL, phone VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, zip_code VARCHAR(255) NOT NULL, creation_date DATETIME NOT NULL, modification_date DATETIME NOT NULL, INDEX IDX_C37E0D4998A7ADBA (sales_company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sales_company_profile_invoices (id INT AUTO_INCREMENT NOT NULL, sales_company_id INT NOT NULL, title VARCHAR(255) NOT NULL, category VARCHAR(255) NOT NULL, taxpayer_id VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, phone VARCHAR(255) NOT NULL, bank_account_name VARCHAR(255) NOT NULL, bank_account_number VARCHAR(255) NOT NULL, creation_date DATETIME NOT NULL, modification_date DATETIME NOT NULL, INDEX IDX_432DEDB898A7ADBA (sales_company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sales_company_profile_account ADD CONSTRAINT FK_A63DE3DD98A7ADBA FOREIGN KEY (sales_company_id) REFERENCES sales_company (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sales_company_profile_express ADD CONSTRAINT FK_C37E0D4998A7ADBA FOREIGN KEY (sales_company_id) REFERENCES sales_company (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sales_company_profile_invoices ADD CONSTRAINT FK_432DEDB898A7ADBA FOREIGN KEY (sales_company_id) REFERENCES sales_company (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE sales_company_profile_account');
        $this->addSql('DROP TABLE sales_company_profile_express');
        $this->addSql('DROP TABLE sales_company_profile_invoices');
    }
}
