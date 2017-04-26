<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161207175329 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE lease_bill DROP FOREIGN KEY FK_467B22FC56C3BBC6');
        $this->addSql('ALTER TABLE lease_bill DROP FOREIGN KEY FK_467B22FC5F004ACF');
        $this->addSql('DROP INDEX IDX_467B22FC5F004ACF ON lease_bill');
        $this->addSql('DROP INDEX IDX_467B22FC56C3BBC6 ON lease_bill');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE lease_bill ADD CONSTRAINT FK_467B22FC56C3BBC6 FOREIGN KEY (drawee) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE lease_bill ADD CONSTRAINT FK_467B22FC5F004ACF FOREIGN KEY (sender) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_467B22FC5F004ACF ON lease_bill (sender)');
        $this->addSql('CREATE INDEX IDX_467B22FC56C3BBC6 ON lease_bill (drawee)');
    }
}
