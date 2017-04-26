<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170207020021 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE finance_bill_attachment (id INT AUTO_INCREMENT NOT NULL, bill_id INT DEFAULT NULL, content LONGTEXT DEFAULT NULL, attachment_type VARCHAR(255) DEFAULT NULL, filename VARCHAR(255) DEFAULT NULL, preview LONGTEXT DEFAULT NULL, size INT DEFAULT NULL, INDEX IDX_BD8FCCCC1A8C12F5 (bill_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE finance_bill_invoice_info (id INT AUTO_INCREMENT NOT NULL, bill_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, category VARCHAR(255) NOT NULL, recipient VARCHAR(255) NOT NULL, phone VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, zip_code VARCHAR(255) NOT NULL, INDEX IDX_C4EF9C381A8C12F5 (bill_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE finance_bill_attachment ADD CONSTRAINT FK_BD8FCCCC1A8C12F5 FOREIGN KEY (bill_id) REFERENCES finance_long_rent_bill (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE finance_bill_invoice_info ADD CONSTRAINT FK_C4EF9C381A8C12F5 FOREIGN KEY (bill_id) REFERENCES finance_long_rent_bill (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE finance_long_rent_service_bill ADD service_fee DOUBLE PRECISION NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE finance_bill_attachment');
        $this->addSql('DROP TABLE finance_bill_invoice_info');
        $this->addSql('ALTER TABLE finance_long_rent_service_bill DROP service_fee');
    }
}
