<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170717081334 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE user_customer_import (id INT AUTO_INCREMENT NOT NULL, serial_number VARCHAR(64) NOT NULL, company_id INT NOT NULL, name VARCHAR(64) DEFAULT NULL, phone_code VARCHAR(16) DEFAULT NULL, phone VARCHAR(64) DEFAULT NULL, email VARCHAR(64) DEFAULT NULL, sex VARCHAR(16) DEFAULT NULL, id_type VARCHAR(64) DEFAULT NULL, id_number VARCHAR(64) DEFAULT NULL, nationality VARCHAR(64) DEFAULT NULL, language VARCHAR(64) DEFAULT NULL, birthday VARCHAR(16) DEFAULT NULL, company_name VARCHAR(64) DEFAULT NULL, position VARCHAR(64) DEFAULT NULL, comment LONGTEXT DEFAULT NULL, status VARCHAR(16) NOT NULL, creation_date DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE user_customer_import');
    }
}
