<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170214030013 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE finance_short_rent_invoice_applications ADD CONSTRAINT FK_DDDB56146084FC9A FOREIGN KEY (official_profile_id) REFERENCES finance_official_invoice_profiles (id)');
        $this->addSql('CREATE INDEX IDX_DDDB56146084FC9A ON finance_short_rent_invoice_applications (official_profile_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE finance_short_rent_invoice_applications DROP FOREIGN KEY FK_DDDB56146084FC9A');
        $this->addSql('DROP INDEX IDX_DDDB56146084FC9A ON finance_short_rent_invoice_applications');
    }
}
