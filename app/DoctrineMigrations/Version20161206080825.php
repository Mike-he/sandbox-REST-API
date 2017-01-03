<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161206080825 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE leases (id INT AUTO_INCREMENT NOT NULL, drawee INT DEFAULT NULL, product_id INT DEFAULT NULL, contact INT DEFAULT NULL, product_appointment_id INT DEFAULT NULL, serial_number VARCHAR(50) DEFAULT NULL, purpose LONGTEXT DEFAULT NULL, other_expenses LONGTEXT DEFAULT NULL, supplementary_terms LONGTEXT DEFAULT NULL, start_date DATETIME DEFAULT NULL, end_date DATETIME DEFAULT NULL, termination_date DATETIME DEFAULT NULL, creation_date DATETIME DEFAULT NULL, modification_date DATETIME DEFAULT NULL, status VARCHAR(15) DEFAULT NULL, monthly_rent NUMERIC(10, 2) DEFAULT NULL, total_rent NUMERIC(10, 2) DEFAULT NULL, deposit NUMERIC(10, 2) DEFAULT NULL, lessee_name VARCHAR(40) DEFAULT NULL, lessee_address VARCHAR(255) DEFAULT NULL, lessee_phone VARCHAR(128) DEFAULT NULL, lessee_email VARCHAR(128) DEFAULT NULL, lessor_name VARCHAR(40) DEFAULT NULL, lessor_address VARCHAR(255) DEFAULT NULL, lessor_phone VARCHAR(128) DEFAULT NULL, lessor_email VARCHAR(128) DEFAULT NULL, INDEX IDX_9B8D6FB456C3BBC6 (drawee), INDEX IDX_9B8D6FB44584665A (product_id), INDEX IDX_9B8D6FB44C62E638 (contact), INDEX IDX_9B8D6FB4B6DC4DD5 (product_appointment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lease_has_rent_types (lease_id INT NOT NULL, lease_rent_types_id INT NOT NULL, INDEX IDX_8FE82657D3CA542C (lease_id), INDEX IDX_8FE826578195DDB4 (lease_rent_types_id), PRIMARY KEY(lease_id, lease_rent_types_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lease_bill (id INT AUTO_INCREMENT NOT NULL, lease_id INT NOT NULL, serial_number VARCHAR(50) DEFAULT NULL, name VARCHAR(40) DEFAULT NULL, start_date DATETIME DEFAULT NULL, end_date DATETIME DEFAULT NULL, creation_date DATETIME NOT NULL, modification_date DATETIME NOT NULL, description LONGTEXT DEFAULT NULL, amount NUMERIC(10, 2) DEFAULT NULL, status VARCHAR(15) DEFAULT NULL, type VARCHAR(10) DEFAULT NULL, revised_amount NUMERIC(10, 2) DEFAULT NULL, revision_note VARCHAR(225) DEFAULT NULL, INDEX IDX_467B22FCD3CA542C (lease_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lease_rent_types (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(16) NOT NULL, name_en VARCHAR(16) NOT NULL, status TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE leases ADD CONSTRAINT FK_9B8D6FB456C3BBC6 FOREIGN KEY (drawee) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE leases ADD CONSTRAINT FK_9B8D6FB44584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE leases ADD CONSTRAINT FK_9B8D6FB44C62E638 FOREIGN KEY (contact) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE leases ADD CONSTRAINT FK_9B8D6FB4B6DC4DD5 FOREIGN KEY (product_appointment_id) REFERENCES product_appointment (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE lease_has_rent_types ADD CONSTRAINT FK_8FE82657D3CA542C FOREIGN KEY (lease_id) REFERENCES leases (id)');
        $this->addSql('ALTER TABLE lease_has_rent_types ADD CONSTRAINT FK_8FE826578195DDB4 FOREIGN KEY (lease_rent_types_id) REFERENCES lease_rent_types (id)');
        $this->addSql('ALTER TABLE lease_bill ADD CONSTRAINT FK_467B22FCD3CA542C FOREIGN KEY (lease_id) REFERENCES leases (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_appointment ADD rent_type VARCHAR(20) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE lease_has_rent_types DROP FOREIGN KEY FK_8FE82657D3CA542C');
        $this->addSql('ALTER TABLE lease_bill DROP FOREIGN KEY FK_467B22FCD3CA542C');
        $this->addSql('ALTER TABLE lease_has_rent_types DROP FOREIGN KEY FK_8FE826578195DDB4');
        $this->addSql('DROP TABLE leases');
        $this->addSql('DROP TABLE lease_has_rent_types');
        $this->addSql('DROP TABLE lease_bill');
        $this->addSql('DROP TABLE lease_rent_types');
        $this->addSql('ALTER TABLE product_appointment DROP rent_type');
    }
}
