<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170419014644 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE duiba_order (id INT AUTO_INCREMENT NOT NULL, app_id INT NOT NULL, user_id INT NOT NULL, credits INT NOT NULL, actual_price DOUBLE PRECISION NOT NULL, duiba_order_num VARCHAR(255) NOT NULL, order_status INT NOT NULL, credits_status INT NOT NULL, type VARCHAR(40) NOT NULL, description VARCHAR(255) NOT NULL, creationDate DATETIME NOT NULL, modificationDate DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_bean_flows (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, type VARCHAR(10) NOT NULL, change_amount DOUBLE PRECISION NOT NULL, balance DOUBLE PRECISION NOT NULL, source VARCHAR(50) NOT NULL, trade_id VARCHAR(50) DEFAULT NULL, creationDate DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user ADD bean DOUBLE PRECISION DEFAULT \'0\' NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE duiba_order');
        $this->addSql('DROP TABLE user_bean_flows');
        $this->addSql('ALTER TABLE user DROP bean');
    }
}
