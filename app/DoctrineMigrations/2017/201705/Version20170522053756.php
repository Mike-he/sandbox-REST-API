<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170522053756 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE room_building ADD lessor_bank_account_name VARCHAR(64) DEFAULT NULL, ADD lessor_bank_account_number VARCHAR(64) DEFAULT NULL, ADD lessor_bank_name VARCHAR(64) DEFAULT NULL, ADD lease_remarks LONGTEXT DEFAULT NULL, ADD postal_code VARCHAR(16) DEFAULT NULL, ADD community_manager_name VARCHAR(16) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE room_building DROP lessor_bank_account_name, DROP lessor_bank_account_number, DROP lessor_bank_name, DROP lease_remarks, DROP postal_code, DROP community_manager_name');
    }
}
