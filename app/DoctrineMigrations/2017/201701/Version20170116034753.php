<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170116034753 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sales_company_withdrawals (id INT AUTO_INCREMENT NOT NULL, sales_company_id INT NOT NULL, sales_company_name VARCHAR(128) NOT NULL, bank_account_name VARCHAR(255) NOT NULL, bank_account_number VARCHAR(255) NOT NULL, creation_date DATETIME NOT NULL, amount DOUBLE PRECISION NOT NULL, modification_date DATETIME NOT NULL, status VARCHAR(16) NOT NULL, successTime DATETIME DEFAULT NULL, failureTime DATETIME DEFAULT NULL, salesAdminId INT NOT NULL, officialAdminId INT DEFAULT NULL, INDEX IDX_FCF53F4A98A7ADBA (sales_company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sales_company_withdrawals ADD CONSTRAINT FK_FCF53F4A98A7ADBA FOREIGN KEY (sales_company_id) REFERENCES sales_company (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE sales_company_withdrawals');
    }
}
