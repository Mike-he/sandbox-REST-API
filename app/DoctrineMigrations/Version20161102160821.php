<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161102160821 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE advertising_attachment DROP FOREIGN KEY FK_413B4709DAEC5E6E');
        $this->addSql('ALTER TABLE advertising_attachment ADD CONSTRAINT FK_413B4709DAEC5E6E FOREIGN KEY (advertisingId) REFERENCES advertising (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE advertising_attachment DROP FOREIGN KEY FK_413B4709DAEC5E6E');
        $this->addSql('ALTER TABLE advertising_attachment ADD CONSTRAINT FK_413B4709DAEC5E6E FOREIGN KEY (advertisingId) REFERENCES advertising (id)');
    }
}
