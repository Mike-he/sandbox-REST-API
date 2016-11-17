<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161117032236 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE product DROP seatNumber, CHANGE basePrice basePrice NUMERIC(10, 0) DEFAULT NULL');
        $this->addSql('ALTER TABLE room_fixed ADD basePrice NUMERIC(10, 0) DEFAULT NULL, DROP available');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE product ADD seatNumber VARCHAR(64) DEFAULT NULL, CHANGE basePrice basePrice NUMERIC(10, 0) NOT NULL');
        $this->addSql('ALTER TABLE room_fixed ADD available TINYINT(1) NOT NULL, DROP basePrice');
    }
}
