<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170209103551 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE finance_short_rent_invoice_applications ADD invoice_no LONGTEXT NOT NULL, ADD confirm_date DATETIME DEFAULT NULL, ADD revoke_date DATETIME DEFAULT NULL, ADD invoice_ids LONGTEXT NOT NULL, DROP invoiceNo, DROP confirmDate, DROP revokeDate, DROP invoiceIds, CHANGE companyid company_id INT NOT NULL, CHANGE creationdate creation_date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE finance_short_rent_invoice_applications ADD CONSTRAINT FK_DDDB5614979B1AD6 FOREIGN KEY (company_id) REFERENCES sales_company (id)');
        $this->addSql('CREATE INDEX IDX_DDDB5614979B1AD6 ON finance_short_rent_invoice_applications (company_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE finance_short_rent_invoice_applications DROP FOREIGN KEY FK_DDDB5614979B1AD6');
        $this->addSql('DROP INDEX IDX_DDDB5614979B1AD6 ON finance_short_rent_invoice_applications');
        $this->addSql('ALTER TABLE finance_short_rent_invoice_applications ADD invoiceNo LONGTEXT NOT NULL, ADD confirmDate DATETIME DEFAULT NULL, ADD revokeDate DATETIME DEFAULT NULL, ADD invoiceIds LONGTEXT NOT NULL, DROP invoice_no, DROP confirm_date, DROP revoke_date, DROP invoice_ids, CHANGE company_id companyId INT NOT NULL, CHANGE creation_date creationDate DATETIME NOT NULL');
    }
}
