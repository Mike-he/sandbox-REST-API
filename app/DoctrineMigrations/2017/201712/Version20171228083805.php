<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171228083805 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE room_building_service_member ADD company_id INT NOT NULL');
        $this->addSql('ALTER TABLE room_building CHANGE commnue_status commnue_status VARCHAR(255) DEFAULT \'normal\' NOT NULL');
        $this->addSql('ALTER TABLE commnue_advertising_micro CHANGE sort_time sort_time VARCHAR(15) NOT NULL');
        $this->addSql('ALTER TABLE commnue_advertising_screen CHANGE source_id source_id INT DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE commnue_advertising_micro CHANGE sort_time sort_time VARCHAR(15) DEFAULT NULL');
        $this->addSql('ALTER TABLE commnue_advertising_screen CHANGE source_id source_id INT NOT NULL');
        $this->addSql('ALTER TABLE room_building CHANGE commnue_status commnue_status VARCHAR(64) DEFAULT \'normal\' NOT NULL');
        $this->addSql('ALTER TABLE room_building_service_member DROP company_id');
    }
}
