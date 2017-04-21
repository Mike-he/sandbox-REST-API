<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170116102905 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sales_company_withdrawals ADD success_time DATETIME DEFAULT NULL, ADD failure_time DATETIME DEFAULT NULL, DROP successTime, DROP failureTime, CHANGE salesadminid sales_admin_id INT NOT NULL, CHANGE officialadminid official_admin_id INT DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sales_company_withdrawals ADD successTime DATETIME DEFAULT NULL, ADD failureTime DATETIME DEFAULT NULL, DROP success_time, DROP failure_time, CHANGE sales_admin_id salesAdminId INT NOT NULL, CHANGE official_admin_id officialAdminId INT DEFAULT NULL');
    }
}
