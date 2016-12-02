<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161202021616 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE product CHANGE basePrice basePrice VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE room_fixed CHANGE seatNumber seatNumber VARCHAR(16) NOT NULL');
        $this->addSql('ALTER TABLE room_types ADD homepageIcon LONGTEXT NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE product CHANGE basePrice basePrice NUMERIC(10, 0) DEFAULT NULL');
        $this->addSql('ALTER TABLE room_fixed CHANGE seatNumber seatNumber INT NOT NULL');
        $this->addSql('ALTER TABLE room_types DROP homepageIcon');
    }
}
