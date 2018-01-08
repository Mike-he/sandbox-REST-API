<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180108071618 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE leases DROP FOREIGN KEY FK_9B8D6FB44D9192F8');
        $this->addSql('ALTER TABLE leases DROP FOREIGN KEY FK_9B8D6FB456C3BBC6');
        $this->addSql('ALTER TABLE leases DROP FOREIGN KEY FK_9B8D6FB4B6DC4DD5');
        $this->addSql('DROP INDEX IDX_9B8D6FB456C3BBC6 ON leases');
        $this->addSql('DROP INDEX IDX_9B8D6FB4B6DC4DD5 ON leases');
        $this->addSql('DROP INDEX IDX_9B8D6FB44D9192F8 ON leases');
        $this->addSql('ALTER TABLE leases DROP supervisor, DROP drawee, DROP product_appointment_id');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE leases ADD supervisor INT DEFAULT NULL, ADD drawee INT DEFAULT NULL, ADD product_appointment_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE leases ADD CONSTRAINT FK_9B8D6FB44D9192F8 FOREIGN KEY (supervisor) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE leases ADD CONSTRAINT FK_9B8D6FB456C3BBC6 FOREIGN KEY (drawee) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE leases ADD CONSTRAINT FK_9B8D6FB4B6DC4DD5 FOREIGN KEY (product_appointment_id) REFERENCES product_appointment (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_9B8D6FB456C3BBC6 ON leases (drawee)');
        $this->addSql('CREATE INDEX IDX_9B8D6FB4B6DC4DD5 ON leases (product_appointment_id)');
        $this->addSql('CREATE INDEX IDX_9B8D6FB44D9192F8 ON leases (supervisor)');
    }
}
