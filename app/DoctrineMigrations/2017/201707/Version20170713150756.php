<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170713150756 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE leases DROP FOREIGN KEY FK_9B8D6FB44584665A');
        $this->addSql('ALTER TABLE leases ADD building_id INT DEFAULT NULL, ADD lease_clue_id INT DEFAULT NULL, ADD lease_offer_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE leases ADD CONSTRAINT FK_9B8D6FB44584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE SET NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE leases DROP FOREIGN KEY FK_9B8D6FB44584665A');
        $this->addSql('ALTER TABLE leases DROP building_id, DROP lease_clue_id, DROP lease_offer_id');
        $this->addSql('ALTER TABLE leases ADD CONSTRAINT FK_9B8D6FB44584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
    }
}
