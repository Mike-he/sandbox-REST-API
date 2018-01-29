<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170712105242 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE lease_clues (id INT AUTO_INCREMENT NOT NULL, serial_number VARCHAR(50) NOT NULL, product_id INT DEFAULT NULL, lessee_name VARCHAR(40) DEFAULT NULL, lessee_address VARCHAR(255) DEFAULT NULL, lessee_customer INT NOT NULL, lessee_phone VARCHAR(128) DEFAULT NULL, lessee_email VARCHAR(128) DEFAULT NULL, start_date DATETIME DEFAULT NULL, end_date DATETIME DEFAULT NULL, cycle INT DEFAULT NULL, monthly_rent NUMERIC(10, 2) DEFAULT NULL, number INT DEFAULT NULL, status VARCHAR(15) DEFAULT NULL, product_appointment_id INT DEFAULT NULL, creation_date DATETIME NOT NULL, modification_date DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE lease_clues');
    }
}
