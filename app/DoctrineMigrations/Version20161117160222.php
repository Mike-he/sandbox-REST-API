<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161117160222 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE room_building DROP FOREIGN KEY FK_4189A11048F7C62E');
        $this->addSql('ALTER TABLE room_building DROP FOREIGN KEY FK_4189A110DFF139D8');
        $this->addSql('ALTER TABLE room_building DROP FOREIGN KEY FK_4189A110FBA2A6B4');
        $this->addSql('DROP INDEX IDX_4189A110FBA2A6B4 ON room_building');
        $this->addSql('DROP INDEX IDX_4189A11048F7C62E ON room_building');
        $this->addSql('DROP INDEX IDX_4189A110DFF139D8 ON room_building');
        $this->addSql('ALTER TABLE room_building ADD districtId INT DEFAULT NULL, DROP countryId, DROP provinceId, DROP areaId');
        $this->addSql('ALTER TABLE room_building ADD CONSTRAINT FK_4189A1103279E952 FOREIGN KEY (districtId) REFERENCES room_city (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_4189A1103279E952 ON room_building (districtId)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE room_building DROP FOREIGN KEY FK_4189A1103279E952');
        $this->addSql('DROP INDEX IDX_4189A1103279E952 ON room_building');
        $this->addSql('ALTER TABLE room_building ADD provinceId INT DEFAULT NULL, ADD areaId INT DEFAULT NULL, CHANGE districtid countryId INT DEFAULT NULL');
        $this->addSql('ALTER TABLE room_building ADD CONSTRAINT FK_4189A11048F7C62E FOREIGN KEY (provinceId) REFERENCES room_city (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE room_building ADD CONSTRAINT FK_4189A110DFF139D8 FOREIGN KEY (areaId) REFERENCES room_city (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE room_building ADD CONSTRAINT FK_4189A110FBA2A6B4 FOREIGN KEY (countryId) REFERENCES room_city (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_4189A110FBA2A6B4 ON room_building (countryId)');
        $this->addSql('CREATE INDEX IDX_4189A11048F7C62E ON room_building (provinceId)');
        $this->addSql('CREATE INDEX IDX_4189A110DFF139D8 ON room_building (areaId)');
    }
}
