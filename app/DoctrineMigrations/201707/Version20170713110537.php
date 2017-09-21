<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170713110537 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE lease_offer (id INT AUTO_INCREMENT NOT NULL, serial_number VARCHAR(50) NOT NULL, building_id INT DEFAULT NULL, product_id INT DEFAULT NULL, lessor_name VARCHAR(40) DEFAULT NULL, lessor_address VARCHAR(255) DEFAULT NULL, lessor_contact VARCHAR(20) DEFAULT NULL, lessor_phone VARCHAR(128) DEFAULT NULL, lessor_email VARCHAR(128) DEFAULT NULL, lessee_type VARCHAR(40) NOT NULL, lessee_enterprise INT DEFAULT NULL, lessee_customer INT NOT NULL, start_date DATETIME DEFAULT NULL, end_date DATETIME DEFAULT NULL, monthly_rent NUMERIC(10, 2) DEFAULT NULL, total_rent NUMERIC(10, 2) DEFAULT NULL, deposit NUMERIC(10, 2) DEFAULT NULL, purpose LONGTEXT DEFAULT NULL, other_expenses LONGTEXT DEFAULT NULL, supplementary_terms LONGTEXT DEFAULT NULL, status VARCHAR(15) NOT NULL, lease_clue_id INT DEFAULT NULL, creation_date DATETIME NOT NULL, modification_date DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lease_offer_has_rent_types (lease_offer_id INT NOT NULL, lease_rent_types_id INT NOT NULL, INDEX IDX_DBE314971CB9F00F (lease_offer_id), INDEX IDX_DBE314978195DDB4 (lease_rent_types_id), PRIMARY KEY(lease_offer_id, lease_rent_types_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE lease_offer_has_rent_types ADD CONSTRAINT FK_DBE314971CB9F00F FOREIGN KEY (lease_offer_id) REFERENCES lease_offer (id)');
        $this->addSql('ALTER TABLE lease_offer_has_rent_types ADD CONSTRAINT FK_DBE314978195DDB4 FOREIGN KEY (lease_rent_types_id) REFERENCES lease_rent_types (id)');
        $this->addSql('ALTER TABLE lease_clues ADD building_id INT DEFAULT NULL, CHANGE status status VARCHAR(15) NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE lease_offer_has_rent_types DROP FOREIGN KEY FK_DBE314971CB9F00F');
        $this->addSql('DROP TABLE lease_offer');
        $this->addSql('DROP TABLE lease_offer_has_rent_types');
        $this->addSql('ALTER TABLE lease_clues DROP building_id, CHANGE status status VARCHAR(15) DEFAULT NULL');
    }
}
