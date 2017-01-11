<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161114025316 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE room_building DROP FOREIGN KEY FK_4189A1107F99FC72');
        $this->addSql('ALTER TABLE room_building CHANGE cityId cityId INT DEFAULT NULL');
        $this->addSql('ALTER TABLE room_building ADD CONSTRAINT FK_4189A1107F99FC72 FOREIGN KEY (cityId) REFERENCES room_city (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA77F99FC72');
        $this->addSql('ALTER TABLE event CHANGE cityId cityId INT DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA77F99FC72 FOREIGN KEY (cityId) REFERENCES room_city (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE food DROP FOREIGN KEY FK_D43829F77F99FC72');
        $this->addSql('ALTER TABLE food CHANGE cityId cityId INT DEFAULT NULL');
        $this->addSql('ALTER TABLE food ADD CONSTRAINT FK_D43829F77F99FC72 FOREIGN KEY (cityId) REFERENCES room_city (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE room DROP FOREIGN KEY FK_729F519B7F99FC72');
        $this->addSql('ALTER TABLE room CHANGE cityId cityId INT DEFAULT NULL');
        $this->addSql('ALTER TABLE room ADD CONSTRAINT FK_729F519B7F99FC72 FOREIGN KEY (cityId) REFERENCES room_city (id) ON DELETE SET NULL');

        $this->addSql('DROP INDEX key_UNIQUE ON room_city');
        $this->addSql('ALTER TABLE room_city ADD parentId INT DEFAULT NULL, ADD level INT NOT NULL, DROP `key`');
        $this->addSql('ALTER TABLE room_city ADD CONSTRAINT FK_4E5FE85010EE4CEE FOREIGN KEY (parentId) REFERENCES room_city (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_4E5FE85010EE4CEE ON room_city (parentId)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
