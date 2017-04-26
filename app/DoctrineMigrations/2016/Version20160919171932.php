<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160919171932 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE user_check_codes (id INT AUTO_INCREMENT NOT NULL, phoneCode VARCHAR(64) DEFAULT NULL, phone VARCHAR(64) DEFAULT \'\', email VARCHAR(128) DEFAULT \'\', type SMALLINT DEFAULT \'0\' NOT NULL, code VARCHAR(6) NOT NULL, creationDate DATETIME NOT NULL, UNIQUE INDEX phone_and_phone_code_UNIQUE (phone, phoneCode), UNIQUE INDEX email_UNIQUE (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE app_info ADD app VARCHAR(16) NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE user_check_codes');
        $this->addSql('ALTER TABLE app_info DROP app');
    }
}
