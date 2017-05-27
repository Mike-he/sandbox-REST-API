<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170502080817 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE room_attachment DROP FOREIGN KEY FK_4DADA9A7F55CF348');
        $this->addSql('ALTER TABLE room_attachment CHANGE buildingId buildingId INT DEFAULT NULL');
        $this->addSql('ALTER TABLE room_attachment ADD CONSTRAINT FK_4DADA9A7F55CF348 FOREIGN KEY (buildingId) REFERENCES room_building (id) ON DELETE SET NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE room_attachment DROP FOREIGN KEY FK_4DADA9A7F55CF348');
        $this->addSql('ALTER TABLE room_attachment CHANGE buildingId buildingId INT NOT NULL');
        $this->addSql('ALTER TABLE room_attachment ADD CONSTRAINT FK_4DADA9A7F55CF348 FOREIGN KEY (buildingId) REFERENCES room_building (id) ON DELETE CASCADE');
    }
}
