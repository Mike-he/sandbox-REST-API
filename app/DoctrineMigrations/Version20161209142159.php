<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161209142159 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE lease_bill_offline_transfer (id INT AUTO_INCREMENT NOT NULL, bill_id INT DEFAULT NULL, account_name VARCHAR(64) DEFAULT NULL, account_no VARCHAR(64) DEFAULT NULL, transfer_status VARCHAR(16) NOT NULL, creation_date DATETIME NOT NULL, modification_date DATETIME NOT NULL, INDEX IDX_C045C72B1A8C12F5 (bill_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lease_bill_transfer_attachment (id INT AUTO_INCREMENT NOT NULL, transfer_id INT DEFAULT NULL, content LONGTEXT DEFAULT NULL, attachment_type VARCHAR(255) DEFAULT NULL, filename VARCHAR(255) DEFAULT NULL, preview LONGTEXT DEFAULT NULL, size INT DEFAULT NULL, INDEX IDX_3416AD62537048AF (transfer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE lease_bill_offline_transfer ADD CONSTRAINT FK_C045C72B1A8C12F5 FOREIGN KEY (bill_id) REFERENCES lease_bill (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lease_bill_transfer_attachment ADD CONSTRAINT FK_3416AD62537048AF FOREIGN KEY (transfer_id) REFERENCES lease_bill_offline_transfer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lease_bill CHANGE paychannel pay_channel VARCHAR(16) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE lease_bill_transfer_attachment DROP FOREIGN KEY FK_3416AD62537048AF');
        $this->addSql('DROP TABLE lease_bill_offline_transfer');
        $this->addSql('DROP TABLE lease_bill_transfer_attachment');
        $this->addSql('ALTER TABLE lease_bill CHANGE pay_channel payChannel VARCHAR(16) DEFAULT NULL');
    }
}
