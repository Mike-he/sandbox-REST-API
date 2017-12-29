<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171229083008 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE expert_order_remark (id INT AUTO_INCREMENT NOT NULL, order_id INT NOT NULL, remark LONGTEXT NOT NULL, creation_date DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE expert (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, name VARCHAR(64) NOT NULL, gender VARCHAR(16) NOT NULL, credential_no VARCHAR(64) NOT NULL, phone VARCHAR(64) NOT NULL, email VARCHAR(128) NOT NULL, country_id INT NOT NULL, city_id INT NOT NULL, province_id INT NOT NULL, district_id INT NOT NULL, base_price DOUBLE PRECISION NOT NULL, is_service TINYINT(1) NOT NULL, banned TINYINT(1) NOT NULL, identity VARCHAR(64) NOT NULL, introduction VARCHAR(256) NOT NULL, description LONGTEXT NOT NULL, photo LONGTEXT DEFAULT NULL, creation_date DATETIME DEFAULT NULL, modification_date DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE expert_has_expert_field (expert_id INT NOT NULL, expert_field_id INT NOT NULL, INDEX IDX_593AAD8AC5568CE4 (expert_id), INDEX IDX_593AAD8AA04BAACE (expert_field_id), PRIMARY KEY(expert_id, expert_field_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE expert_order (id INT AUTO_INCREMENT NOT NULL, expert_id INT NOT NULL, order_number VARCHAR(64) NOT NULL, price DOUBLE PRECISION NOT NULL, status VARCHAR(64) NOT NULL, completed_date DATETIME DEFAULT NULL, cancelled_date DATETIME DEFAULT NULL, creation_date DATETIME DEFAULT NULL, modification_date DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE expert_field (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(64) NOT NULL, description LONGTEXT NOT NULL, creation_date DATETIME DEFAULT NULL, modification_date DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE expert_has_expert_field ADD CONSTRAINT FK_593AAD8AC5568CE4 FOREIGN KEY (expert_id) REFERENCES expert (id)');
        $this->addSql('ALTER TABLE expert_has_expert_field ADD CONSTRAINT FK_593AAD8AA04BAACE FOREIGN KEY (expert_field_id) REFERENCES expert_field (id)');
        $this->addSql('ALTER TABLE view_count ADD type VARCHAR(64) NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE expert_has_expert_field DROP FOREIGN KEY FK_593AAD8AC5568CE4');
        $this->addSql('ALTER TABLE expert_has_expert_field DROP FOREIGN KEY FK_593AAD8AA04BAACE');
        $this->addSql('DROP TABLE expert_order_remark');
        $this->addSql('DROP TABLE expert');
        $this->addSql('DROP TABLE expert_has_expert_field');
        $this->addSql('DROP TABLE expert_order');
        $this->addSql('DROP TABLE expert_field');
        $this->addSql('ALTER TABLE view_count DROP type');
    }
}
