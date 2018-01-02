<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171026080314 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE finance_short_rent_invoice_applications');
        $this->addSql('ALTER TABLE finance_sales_wallet DROP short_rent_invoice_amount');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE finance_short_rent_invoice_applications (id INT AUTO_INCREMENT NOT NULL, official_profile_id INT NOT NULL, company_id INT NOT NULL, amount DOUBLE PRECISION NOT NULL, status VARCHAR(16) NOT NULL, creation_date DATETIME NOT NULL, invoice_no LONGTEXT NOT NULL, confirm_date DATETIME DEFAULT NULL, revoke_date DATETIME DEFAULT NULL, invoice_ids LONGTEXT NOT NULL, INDEX IDX_DDDB5614979B1AD6 (company_id), INDEX IDX_DDDB56146084FC9A (official_profile_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE finance_short_rent_invoice_applications ADD CONSTRAINT FK_DDDB56146084FC9A FOREIGN KEY (official_profile_id) REFERENCES finance_official_invoice_profiles (id)');
        $this->addSql('ALTER TABLE finance_short_rent_invoice_applications ADD CONSTRAINT FK_DDDB5614979B1AD6 FOREIGN KEY (company_id) REFERENCES sales_company (id)');
        $this->addSql('ALTER TABLE finance_sales_wallet ADD short_rent_invoice_amount DOUBLE PRECISION NOT NULL');
    }
}
