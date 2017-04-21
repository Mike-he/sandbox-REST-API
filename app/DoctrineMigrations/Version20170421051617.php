<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170421051617 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE offline_transfer (id INT AUTO_INCREMENT NOT NULL, order_number VARCHAR(128) NOT NULL, type VARCHAR(32) NOT NULL, price DOUBLE PRECISION NOT NULL, user_id INT NOT NULL, account_name VARCHAR(64) DEFAULT NULL, account_no VARCHAR(64) DEFAULT NULL, transfer_status VARCHAR(16) NOT NULL, creationDate DATETIME NOT NULL, modificationDate DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE offline_transfer_attachment (id INT AUTO_INCREMENT NOT NULL, transfer_id INT DEFAULT NULL, content LONGTEXT DEFAULT NULL, attachment_type VARCHAR(255) DEFAULT NULL, filename VARCHAR(255) DEFAULT NULL, preview LONGTEXT DEFAULT NULL, size INT DEFAULT NULL, INDEX IDX_48D8E173537048AF (transfer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE offline_transfer_attachment ADD CONSTRAINT FK_48D8E173537048AF FOREIGN KEY (transfer_id) REFERENCES offline_transfer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_bean_flows CHANGE change_amount change_amount VARCHAR(20) NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE offline_transfer_attachment DROP FOREIGN KEY FK_48D8E173537048AF');
        $this->addSql('DROP TABLE offline_transfer');
        $this->addSql('DROP TABLE offline_transfer_attachment');
        $this->addSql('ALTER TABLE user_bean_flows CHANGE change_amount change_amount DOUBLE PRECISION NOT NULL');
    }
}
