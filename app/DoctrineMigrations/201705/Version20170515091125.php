<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170515091125 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE room_types_groups (id INT AUTO_INCREMENT NOT NULL, group_key VARCHAR(64) NOT NULL, icon VARCHAR(255) NOT NULL, homepage_icon VARCHAR(255) NOT NULL, creation_date DATETIME NOT NULL, modification_date DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE room_types ADD group_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE room_types ADD CONSTRAINT FK_138C289BFE54D947 FOREIGN KEY (group_id) REFERENCES room_types_groups (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_138C289BFE54D947 ON room_types (group_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE room_types DROP FOREIGN KEY FK_138C289BFE54D947');
        $this->addSql('DROP TABLE room_types_groups');
        $this->addSql('DROP INDEX IDX_138C289BFE54D947 ON room_types');
        $this->addSql('ALTER TABLE room_types DROP group_id');
    }
}
