<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161215094905 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE door_access CHANGE orderid accessNo INT NOT NULL');
        $this->addSql('ALTER TABLE door_access CHANGE accessNo accessNo VARCHAR(30) NOT NULL');
        $this->addSql('ALTER TABLE leases ADD access_no VARCHAR(30) DEFAULT NULL');
        $this->addSql('ALTER TABLE leases DROP FOREIGN KEY FK_9B8D6FB44584665A');
        $this->addSql('ALTER TABLE leases ADD CONSTRAINT FK_9B8D6FB44584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE door_access CHANGE accessno orderId INT NOT NULL');
        $this->addSql('ALTER TABLE door_access CHANGE accessNo accessNo INT NOT NULL');
        $this->addSql('ALTER TABLE leases DROP access_no');
        $this->addSql('ALTER TABLE leases DROP FOREIGN KEY FK_9B8D6FB44584665A');
        $this->addSql('ALTER TABLE leases ADD CONSTRAINT FK_9B8D6FB44584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE SET NULL');
    }
}
