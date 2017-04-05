<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170405085505 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE membership_card (id INT AUTO_INCREMENT NOT NULL, access_no VARCHAR(64) NOT NULL, name VARCHAR(64) NOT NULL, background VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, instructions LONGTEXT NOT NULL, phone VARCHAR(64) NOT NULL, visible TINYINT(1) NOT NULL, company_id INT NOT NULL, creation_date DATETIME NOT NULL, modification_date DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE membership_card_specification (id INT AUTO_INCREMENT NOT NULL, card_id INT DEFAULT NULL, specification VARCHAR(64) NOT NULL, price NUMERIC(10, 2) NOT NULL, valid_period INT NOT NULL, unit_price VARCHAR(255) NOT NULL, creation_date DATETIME NOT NULL, modification_date DATETIME NOT NULL, INDEX IDX_525FD2BB4ACC9A20 (card_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_group (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(64) NOT NULL, card_id INT DEFAULT NULL, type VARCHAR(64) NOT NULL, description LONGTEXT DEFAULT NULL, company_id INT NOT NULL, creation_date DATETIME NOT NULL, modification_date DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_group_has_user (group_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_B27A9E30FE54D947 (group_id), INDEX IDX_B27A9E30A76ED395 (user_id), PRIMARY KEY(group_id, user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_group_doors (id INT AUTO_INCREMENT NOT NULL, building_id INT NOT NULL, door_control_id VARCHAR(255) NOT NULL, name VARCHAR(64) NOT NULL, group_id INT NOT NULL, card_id INT DEFAULT NULL, creationDate DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE membership_card_specification ADD CONSTRAINT FK_525FD2BB4ACC9A20 FOREIGN KEY (card_id) REFERENCES membership_card (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_group_has_user ADD CONSTRAINT FK_B27A9E30FE54D947 FOREIGN KEY (group_id) REFERENCES user_group (id)');
        $this->addSql('ALTER TABLE user_group_has_user ADD CONSTRAINT FK_B27A9E30A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE membership_order ADD card_id INT DEFAULT NULL, ADD order_number VARCHAR(64) NOT NULL, ADD user_id INT NOT NULL, ADD start_date DATETIME NOT NULL, ADD end_date DATETIME NOT NULL, ADD valid_period INT NOT NULL, ADD specification VARCHAR(64) NOT NULL, ADD status VARCHAR(64) NOT NULL, ADD pay_channel VARCHAR(16) DEFAULT NULL, ADD payment_date DATETIME DEFAULT NULL, ADD cancelled_date DATETIME DEFAULT NULL, ADD invoiced TINYINT(1) DEFAULT \'0\' NOT NULL, ADD sales_invoice TINYINT(1) DEFAULT \'0\' NOT NULL, ADD service_fee DOUBLE PRECISION DEFAULT \'0\' NOT NULL, ADD creation_date DATETIME NOT NULL, ADD modification_date DATETIME NOT NULL, DROP productId, DROP userId, DROP creationDate, DROP modificationDate, CHANGE ordernumber unit_price VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE membership_order ADD CONSTRAINT FK_F9E4B7954ACC9A20 FOREIGN KEY (card_id) REFERENCES membership_card (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_F9E4B7954ACC9A20 ON membership_order (card_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE membership_card_specification DROP FOREIGN KEY FK_525FD2BB4ACC9A20');
        $this->addSql('ALTER TABLE membership_order DROP FOREIGN KEY FK_F9E4B7954ACC9A20');
        $this->addSql('ALTER TABLE user_group_has_user DROP FOREIGN KEY FK_B27A9E30FE54D947');
        $this->addSql('DROP TABLE membership_card');
        $this->addSql('DROP TABLE membership_card_specification');
        $this->addSql('DROP TABLE user_group');
        $this->addSql('DROP TABLE user_group_has_user');
        $this->addSql('DROP TABLE user_group_doors');
        $this->addSql('DROP INDEX IDX_F9E4B7954ACC9A20 ON membership_order');
        $this->addSql('ALTER TABLE membership_order ADD productId INT NOT NULL, ADD userId INT NOT NULL, ADD creationDate DATETIME NOT NULL, ADD modificationDate DATETIME NOT NULL, DROP card_id, DROP order_number, DROP user_id, DROP start_date, DROP end_date, DROP valid_period, DROP specification, DROP status, DROP pay_channel, DROP payment_date, DROP cancelled_date, DROP invoiced, DROP sales_invoice, DROP service_fee, DROP creation_date, DROP modification_date, CHANGE unit_price orderNumber VARCHAR(255) NOT NULL');
    }
}
