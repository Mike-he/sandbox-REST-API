<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170314023256 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE product_order DROP productInfo');
        $this->addSql('ALTER TABLE product_order_info CHANGE order_id order_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product_order_info ADD CONSTRAINT FK_151F546B8D9F6D38 FOREIGN KEY (order_id) REFERENCES product_order (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_151F546B8D9F6D38 ON product_order_info (order_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE product_order ADD productInfo LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE product_order_info DROP FOREIGN KEY FK_151F546B8D9F6D38');
        $this->addSql('DROP INDEX UNIQ_151F546B8D9F6D38 ON product_order_info');
        $this->addSql('ALTER TABLE product_order_info CHANGE order_id order_id INT NOT NULL');
    }
}
