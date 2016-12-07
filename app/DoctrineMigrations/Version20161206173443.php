<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161206173443 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE user_appointment_profiles (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, name VARCHAR(64) NOT NULL, contact VARCHAR(64) NOT NULL, email VARCHAR(128) DEFAULT NULL, phone VARCHAR(128) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, creationDate DATETIME NOT NULL, modificationDate DATETIME NOT NULL, INDEX IDX_35DD87A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_appointment_profiles ADD CONSTRAINT FK_35DD87A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE room CHANGE type type VARCHAR(64) NOT NULL');
        $this->addSql('ALTER TABLE room_attachment CHANGE roomType roomType VARCHAR(64) NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE user_appointment_profiles');
        $this->addSql('ALTER TABLE room CHANGE type type VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE room_attachment CHANGE roomType roomType VARCHAR(255) NOT NULL');
    }
}
