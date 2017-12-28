<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171227095905 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE commnue_advertising_micro CHANGE sort_time sort_time VARCHAR(15) NOT NULL');
        $this->addSql('ALTER TABLE room_building CHANGE commnue_status commnue_status VARCHAR(255) NOT NULL');
        $this->addSql('CREATE INDEX IDX_7332E169F92F3E70 ON services (country_id)');
        $this->addSql('ALTER TABLE services ADD CONSTRAINT FK_7332E169F92F3E70 FOREIGN KEY (country_id) REFERENCES room_city (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE services ADD CONSTRAINT FK_7332E1698BAC62AF FOREIGN KEY (city_id) REFERENCES room_city (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE services ADD CONSTRAINT FK_7332E169E946114A FOREIGN KEY (province_id) REFERENCES room_city (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE services ADD CONSTRAINT FK_7332E169B08FA272 FOREIGN KEY (district_id) REFERENCES room_city (id) ON DELETE SET NULL');
       // $this->addSql('CREATE INDEX IDX_7332E169F92F3E70 ON services (country_id)');
        $this->addSql('ALTER TABLE service_attachment ADD CONSTRAINT FK_EF0EE00FED5CA9E6 FOREIGN KEY (service_id) REFERENCES services (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE service_form ADD CONSTRAINT FK_9CCB49A3ED5CA9E6 FOREIGN KEY (service_id) REFERENCES services (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE service_form_option ADD CONSTRAINT FK_94F2F2B25FF69B7D FOREIGN KEY (form_id) REFERENCES service_form (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE service_order ADD CONSTRAINT FK_5C5B7E7FED5CA9E6 FOREIGN KEY (service_id) REFERENCES services (id)');
        $this->addSql('ALTER TABLE service_purchase_form ADD CONSTRAINT FK_F2848FAE8D9F6D38 FOREIGN KEY (order_id) REFERENCES service_order (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE service_purchase_form ADD CONSTRAINT FK_F2848FAE5FF69B7D FOREIGN KEY (form_id) REFERENCES service_form (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE service_times ADD CONSTRAINT FK_B4A5036BED5CA9E6 FOREIGN KEY (service_id) REFERENCES services (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE commnue_advertising_micro CHANGE sort_time sort_time VARCHAR(15) DEFAULT NULL');
        $this->addSql('ALTER TABLE room_building CHANGE commnue_status commnue_status VARCHAR(64) DEFAULT \'normal\' NOT NULL');
        $this->addSql('ALTER TABLE service_attachment DROP FOREIGN KEY FK_EF0EE00FED5CA9E6');
        $this->addSql('ALTER TABLE service_form DROP FOREIGN KEY FK_9CCB49A3ED5CA9E6');
        $this->addSql('ALTER TABLE service_form_option DROP FOREIGN KEY FK_94F2F2B25FF69B7D');
        $this->addSql('ALTER TABLE service_order DROP FOREIGN KEY FK_5C5B7E7FED5CA9E6');
        $this->addSql('ALTER TABLE service_purchase_form DROP FOREIGN KEY FK_F2848FAE8D9F6D38');
        $this->addSql('ALTER TABLE service_purchase_form DROP FOREIGN KEY FK_F2848FAE5FF69B7D');
        $this->addSql('ALTER TABLE service_times DROP FOREIGN KEY FK_B4A5036BED5CA9E6');
        $this->addSql('ALTER TABLE services DROP FOREIGN KEY FK_7332E169F92F3E70');
        $this->addSql('ALTER TABLE services DROP FOREIGN KEY FK_7332E1698BAC62AF');
        $this->addSql('ALTER TABLE services DROP FOREIGN KEY FK_7332E169E946114A');
        $this->addSql('ALTER TABLE services DROP FOREIGN KEY FK_7332E169B08FA272');
        $this->addSql('DROP INDEX IDX_7332E169F92F3E70 ON services');
    }
}
