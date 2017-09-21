<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170811104514 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE finance_long_rent_service_bill DROP FOREIGN KEY FK_D004F8171A8C12F5');
        $this->addSql('ALTER TABLE finance_long_rent_service_bill ADD order_number VARCHAR(64) NOT NULL');
        $this->addSql('ALTER TABLE finance_long_rent_service_bill ADD CONSTRAINT FK_D004F8171A8C12F5 FOREIGN KEY (bill_id) REFERENCES lease_bill (id) ON DELETE SET NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE finance_long_rent_service_bill DROP FOREIGN KEY FK_D004F8171A8C12F5');
        $this->addSql('ALTER TABLE finance_long_rent_service_bill DROP order_number');
        $this->addSql('ALTER TABLE finance_long_rent_service_bill ADD CONSTRAINT FK_D004F8171A8C12F5 FOREIGN KEY (bill_id) REFERENCES lease_bill (id) ON DELETE CASCADE');
    }
}
