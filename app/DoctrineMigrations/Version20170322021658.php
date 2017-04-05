<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170322021658 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE room DROP FOREIGN KEY FK_729F519B4E0A837A');
        $this->addSql('ALTER TABLE room DROP FOREIGN KEY FK_729F519BF55CF348');
        $this->addSql('ALTER TABLE room ADD CONSTRAINT FK_729F519B4E0A837A FOREIGN KEY (floorId) REFERENCES room_floor (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE room ADD CONSTRAINT FK_729F519BF55CF348 FOREIGN KEY (buildingId) REFERENCES room_building (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE room DROP FOREIGN KEY FK_729F519BF55CF348');
        $this->addSql('ALTER TABLE room DROP FOREIGN KEY FK_729F519B4E0A837A');
        $this->addSql('ALTER TABLE room ADD CONSTRAINT FK_729F519BF55CF348 FOREIGN KEY (buildingId) REFERENCES room_building (id)');
        $this->addSql('ALTER TABLE room ADD CONSTRAINT FK_729F519B4E0A837A FOREIGN KEY (floorId) REFERENCES room_floor (id)');
    }
}
