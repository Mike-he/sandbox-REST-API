<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170811015944 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sales_company_profile_account DROP FOREIGN KEY FK_A63DE3DD98A7ADBA');
        $this->addSql('DROP INDEX IDX_A63DE3DD98A7ADBA ON sales_company_profile_account');
        $this->addSql('ALTER TABLE sales_company_profile_account CHANGE business_scope business_scope VARCHAR(1024) DEFAULT NULL, CHANGE bank_account_name bank_account_name VARCHAR(255) DEFAULT NULL, CHANGE bank_account_number bank_account_number VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE sales_company_profile_express DROP FOREIGN KEY FK_C37E0D4998A7ADBA');
        $this->addSql('DROP INDEX IDX_C37E0D4998A7ADBA ON sales_company_profile_express');
        $this->addSql('ALTER TABLE sales_company_profile_express CHANGE recipient recipient VARCHAR(255) DEFAULT NULL, CHANGE phone phone VARCHAR(255) DEFAULT NULL, CHANGE address address VARCHAR(255) DEFAULT NULL, CHANGE zip_code zip_code VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE sales_company_profile_invoices DROP FOREIGN KEY FK_432DEDB898A7ADBA');
        $this->addSql('DROP INDEX IDX_432DEDB898A7ADBA ON sales_company_profile_invoices');
        $this->addSql('ALTER TABLE sales_company_profile_invoices CHANGE title title VARCHAR(255) DEFAULT NULL, CHANGE category category VARCHAR(255) DEFAULT NULL, CHANGE taxpayer_id taxpayer_id VARCHAR(255) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sales_company_profile_account CHANGE business_scope business_scope VARCHAR(1024) NOT NULL, CHANGE bank_account_name bank_account_name VARCHAR(255) NOT NULL, CHANGE bank_account_number bank_account_number VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE sales_company_profile_account ADD CONSTRAINT FK_A63DE3DD98A7ADBA FOREIGN KEY (sales_company_id) REFERENCES sales_company (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_A63DE3DD98A7ADBA ON sales_company_profile_account (sales_company_id)');
        $this->addSql('ALTER TABLE sales_company_profile_express CHANGE recipient recipient VARCHAR(255) NOT NULL, CHANGE phone phone VARCHAR(255) NOT NULL, CHANGE address address VARCHAR(255) NOT NULL, CHANGE zip_code zip_code VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE sales_company_profile_express ADD CONSTRAINT FK_C37E0D4998A7ADBA FOREIGN KEY (sales_company_id) REFERENCES sales_company (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_C37E0D4998A7ADBA ON sales_company_profile_express (sales_company_id)');
        $this->addSql('ALTER TABLE sales_company_profile_invoices CHANGE title title VARCHAR(255) NOT NULL, CHANGE category category VARCHAR(255) NOT NULL, CHANGE taxpayer_id taxpayer_id VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE sales_company_profile_invoices ADD CONSTRAINT FK_432DEDB898A7ADBA FOREIGN KEY (sales_company_id) REFERENCES sales_company (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_432DEDB898A7ADBA ON sales_company_profile_invoices (sales_company_id)');
    }
}
