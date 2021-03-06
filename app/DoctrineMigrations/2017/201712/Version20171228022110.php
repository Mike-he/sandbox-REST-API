<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171228022110 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX IDX_7332E1698BAC62AF ON services');
        $this->addSql('DROP INDEX IDX_7332E169E946114A ON services');
        $this->addSql('DROP INDEX IDX_7332E169B08FA272 ON services');
        $this->addSql('ALTER TABLE services CHANGE publishcompany publish_company VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE service_purchase_form ADD CONSTRAINT FK_F2848FAE8D9F6D38 FOREIGN KEY (order_id) REFERENCES service_order (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE service_purchase_form ADD CONSTRAINT FK_F2848FAE5FF69B7D FOREIGN KEY (form_id) REFERENCES service_form (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE service_times ADD CONSTRAINT FK_B4A5036BED5CA9E6 FOREIGN KEY (service_id) REFERENCES services (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE service_order ADD CONSTRAINT FK_5C5B7E7FED5CA9E6 FOREIGN KEY (service_id) REFERENCES services (id)');
        $this->addSql('ALTER TABLE service_form_option ADD CONSTRAINT FK_94F2F2B25FF69B7D FOREIGN KEY (form_id) REFERENCES service_form (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE service_attachment ADD CONSTRAINT FK_EF0EE00FED5CA9E6 FOREIGN KEY (service_id) REFERENCES services (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE service_attachment DROP FOREIGN KEY FK_EF0EE00FED5CA9E6');
        $this->addSql('ALTER TABLE service_form_option DROP FOREIGN KEY FK_94F2F2B25FF69B7D');
        $this->addSql('ALTER TABLE service_order DROP FOREIGN KEY FK_5C5B7E7FED5CA9E6');
        $this->addSql('ALTER TABLE service_purchase_form DROP FOREIGN KEY FK_F2848FAE8D9F6D38');
        $this->addSql('ALTER TABLE service_purchase_form DROP FOREIGN KEY FK_F2848FAE5FF69B7D');
        $this->addSql('ALTER TABLE service_times DROP FOREIGN KEY FK_B4A5036BED5CA9E6');
        $this->addSql('ALTER TABLE services CHANGE publish_company publishCompany VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_7332E1698BAC62AF ON services (city_id)');
        $this->addSql('CREATE INDEX IDX_7332E169E946114A ON services (province_id)');
        $this->addSql('CREATE INDEX IDX_7332E169B08FA272 ON services (district_id)');
    }
}
