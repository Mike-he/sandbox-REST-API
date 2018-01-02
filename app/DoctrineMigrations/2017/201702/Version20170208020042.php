<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170208020042 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE finance_bill_invoice_info ADD invoice_json LONGTEXT NOT NULL, ADD express_json LONGTEXT NOT NULL, DROP title, DROP category, DROP recipient, DROP phone, DROP address, DROP zip_code');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE finance_bill_invoice_info ADD title VARCHAR(255) NOT NULL, ADD category VARCHAR(255) NOT NULL, ADD recipient VARCHAR(255) NOT NULL, ADD phone VARCHAR(255) NOT NULL, ADD address VARCHAR(255) NOT NULL, ADD zip_code VARCHAR(255) NOT NULL, DROP invoice_json, DROP express_json');
    }
}
