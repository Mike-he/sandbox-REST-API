<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161209175043 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE leases DROP FOREIGN KEY FK_9B8D6FB44C62E638');
        $this->addSql('DROP INDEX IDX_9B8D6FB44C62E638 ON leases');
        $this->addSql('ALTER TABLE leases ADD lessee_contact VARCHAR(20) DEFAULT NULL, ADD lessor_contact VARCHAR(20) DEFAULT NULL, CHANGE contact supervisor INT DEFAULT NULL');
        $this->addSql('ALTER TABLE leases ADD CONSTRAINT FK_9B8D6FB44D9192F8 FOREIGN KEY (supervisor) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_9B8D6FB44D9192F8 ON leases (supervisor)');
        $this->addSql('ALTER TABLE lease_rent_types CHANGE name name VARCHAR(100) NOT NULL, CHANGE name_en name_en VARCHAR(100) NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE lease_rent_types CHANGE name name VARCHAR(16) NOT NULL, CHANGE name_en name_en VARCHAR(16) NOT NULL');
        $this->addSql('ALTER TABLE leases DROP FOREIGN KEY FK_9B8D6FB44D9192F8');
        $this->addSql('DROP INDEX IDX_9B8D6FB44D9192F8 ON leases');
        $this->addSql('ALTER TABLE leases DROP lessee_contact, DROP lessor_contact, CHANGE supervisor contact INT DEFAULT NULL');
        $this->addSql('ALTER TABLE leases ADD CONSTRAINT FK_9B8D6FB44C62E638 FOREIGN KEY (contact) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_9B8D6FB44C62E638 ON leases (contact)');
    }
}
