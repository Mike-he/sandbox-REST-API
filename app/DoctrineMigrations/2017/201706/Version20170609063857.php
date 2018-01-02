<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170609063857 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE product DROP basePrice, DROP unitPrice, DROP isAnnualRent, DROP annualRentUnitPrice, DROP annualRentUnit, DROP annualRentDescription, DROP earliestRentDate, DROP deposit, DROP rentalInfo, DROP filename');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE product ADD basePrice NUMERIC(10, 2) DEFAULT NULL, ADD unitPrice VARCHAR(255) DEFAULT NULL, ADD isAnnualRent TINYINT(1) NOT NULL, ADD annualRentUnitPrice NUMERIC(10, 2) DEFAULT NULL, ADD annualRentUnit VARCHAR(64) DEFAULT NULL, ADD annualRentDescription LONGTEXT DEFAULT NULL, ADD earliestRentDate DATETIME DEFAULT NULL, ADD deposit NUMERIC(10, 2) DEFAULT NULL, ADD rentalInfo LONGTEXT DEFAULT NULL, ADD filename LONGTEXT DEFAULT NULL');
    }
}
