<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170810065315 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sales_company_profiles (id INT AUTO_INCREMENT NOT NULL, sales_company_id INT NOT NULL, cover VARCHAR(255) DEFAULT NULL, creation_date DATETIME NOT NULL, modification_date DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sales_company_profile_account ADD profile_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sales_company_profile_express ADD profile_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sales_company_profile_invoices ADD profile_id INT DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE sales_company_profiles');
        $this->addSql('ALTER TABLE sales_company_profile_account DROP profile_id');
        $this->addSql('ALTER TABLE sales_company_profile_express DROP profile_id');
        $this->addSql('ALTER TABLE sales_company_profile_invoices DROP profile_id');
    }
}
