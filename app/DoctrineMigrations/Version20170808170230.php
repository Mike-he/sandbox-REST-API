<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170808170230 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE product_order DROP FOREIGN KEY FK_5475E8C464B64DCC');
        $this->addSql('ALTER TABLE product_order ADD base_price NUMERIC(10, 2) NOT NULL, ADD unit_price VARCHAR(255) NOT NULL, ADD customer_id INT DEFAULT NULL, CHANGE userId userId INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product_order ADD CONSTRAINT FK_5475E8C464B64DCC FOREIGN KEY (userId) REFERENCES user (id) ON DELETE SET NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE product_order DROP FOREIGN KEY FK_5475E8C464B64DCC');
        $this->addSql('ALTER TABLE product_order DROP base_price, DROP unit_price, DROP customer_id, CHANGE userId userId INT NOT NULL');
        $this->addSql('ALTER TABLE product_order ADD CONSTRAINT FK_5475E8C464B64DCC FOREIGN KEY (userId) REFERENCES user (id) ON DELETE CASCADE');
    }
}
