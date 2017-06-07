<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170522024800 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE product_rent_set (id INT AUTO_INCREMENT NOT NULL, product_id INT DEFAULT NULL, base_price NUMERIC(10, 2) NOT NULL, unit_price VARCHAR(255) NOT NULL, earliest_rent_date DATETIME NOT NULL, deposit NUMERIC(10, 2) NOT NULL, rental_info LONGTEXT NOT NULL, filename LONGTEXT DEFAULT NULL, creationDate DATETIME NOT NULL, modificationDate DATETIME NOT NULL, INDEX IDX_2DFE8B4E4584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product_rent_set ADD CONSTRAINT FK_2DFE8B4E4584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE product_rent_set');
    }
}
