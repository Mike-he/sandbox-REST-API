<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171227021521 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE services (id INT AUTO_INCREMENT NOT NULL, sub_title VARCHAR(255) NOT NULL, country_id INT NOT NULL, city_id INT NOT NULL, province_id INT NOT NULL, district_id INT NOT NULL, name VARCHAR(255) NOT NULL,type_id INT NOT NULL, description LONGTEXT NOT NULL, limit_number INT NOT NULL, service_start_date DATETIME NOT NULL, service_end_date DATETIME NOT NULL, publishCompany VARCHAR(255) DEFAULT NULL, is_charge TINYINT(1) NOT NULL, price NUMERIC(10, 2) DEFAULT NULL, visible TINYINT(1) NOT NULL, is_saved TINYINT(1) NOT NULL, sales_company_id INT NOT NULL, creation_date DATETIME NOT NULL, modification_date DATETIME NOT NULL, status VARCHAR(64) NOT NULL, INDEX IDX_7332E1698BAC62AF (city_id), INDEX IDX_7332E169E946114A (province_id), INDEX IDX_7332E169B08FA272 (district_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE service_attachment (id INT AUTO_INCREMENT NOT NULL, service_id INT NOT NULL, content LONGTEXT NOT NULL, attachment_type VARCHAR(64) NOT NULL, filename VARCHAR(255) NOT NULL, preview LONGTEXT DEFAULT NULL, size INT NOT NULL, INDEX IDX_EF0EE00FED5CA9E6 (service_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE service_form (id INT AUTO_INCREMENT NOT NULL, service_id INT NOT NULL, title VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, INDEX IDX_9CCB49A3ED5CA9E6 (service_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE service_form_option (id INT AUTO_INCREMENT NOT NULL, form_id INT NOT NULL, content LONGTEXT NOT NULL, INDEX IDX_94F2F2B25FF69B7D (form_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE service_order (id INT AUTO_INCREMENT NOT NULL, service_id INT NOT NULL, order_number VARCHAR(255) NOT NULL, pay_channel VARCHAR(255) DEFAULT NULL, user_id INT NOT NULL, company_id INT NOT NULL, price DOUBLE PRECISION NOT NULL, status VARCHAR(64) NOT NULL, payment_date DATETIME DEFAULT NULL, cancelled_date DATETIME DEFAULT NULL, creation_date DATETIME NOT NULL, modification_date DATETIME NOT NULL, service_fee DOUBLE PRECISION DEFAULT \'0\' NOT NULL, customer_id INT DEFAULT NULL, INDEX IDX_5C5B7E7FED5CA9E6 (service_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE service_purchase_form (id INT AUTO_INCREMENT NOT NULL, order_id INT NOT NULL, form_Id INT NOT NULL, user_input LONGTEXT NOT NULL, INDEX IDX_F2848FAE8D9F6D38 (order_id), INDEX IDX_F2848FAE5FF69B7D (form_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE service_times (id INT AUTO_INCREMENT NOT NULL, service_id INT NOT NULL, start_time TIME NOT NULL, end_time TIME NOT NULL, description LONGTEXT NOT NULL, INDEX IDX_B4A5036BED5CA9E6 (service_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE service_types (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE view_count (id INT AUTO_INCREMENT NOT NULL, object VARCHAR(64) NOT NULL, object_id INT NOT NULL, count INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE services');
        $this->addSql('DROP TABLE service_attachment');
        $this->addSql('DROP TABLE service_form');
        $this->addSql('DROP TABLE service_form_option');
        $this->addSql('DROP TABLE service_order');
        $this->addSql('DROP TABLE service_purchase_form');
        $this->addSql('DROP TABLE service_times');
        $this->addSql('DROP TABLE service_types');
        $this->addSql('DROP TABLE view_count');
    }
}
