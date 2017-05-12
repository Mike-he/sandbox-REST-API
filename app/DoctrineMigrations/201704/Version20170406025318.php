<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170406025318 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE membership_order_info (id INT AUTO_INCREMENT NOT NULL, order_id INT DEFAULT NULL, card_info LONGTEXT NOT NULL, creation_date DATETIME NOT NULL, modification_date DATETIME NOT NULL, UNIQUE INDEX UNIQ_647ABD878D9F6D38 (order_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE membership_order_info ADD CONSTRAINT FK_647ABD878D9F6D38 FOREIGN KEY (order_id) REFERENCES membership_order (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE membership_order DROP specification, CHANGE valid_period amount INT NOT NULL, CHANGE unit_price unit VARCHAR(255) NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE membership_order_info');
        $this->addSql('ALTER TABLE membership_order ADD specification VARCHAR(64) NOT NULL, CHANGE unit unit_price VARCHAR(255) NOT NULL, CHANGE amount valid_period INT NOT NULL');
    }
}
