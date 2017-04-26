<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161116080848 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE admin_permission ADD parentId INT DEFAULT NULL');
        $this->addSql('ALTER TABLE admin_permission ADD CONSTRAINT FK_2877342F10EE4CEE FOREIGN KEY (parentId) REFERENCES admin_permission (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_2877342F10EE4CEE ON admin_permission (parentId)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE admin_permission DROP FOREIGN KEY FK_2877342F10EE4CEE');
        $this->addSql('DROP INDEX IDX_2877342F10EE4CEE ON admin_permission');
        $this->addSql('ALTER TABLE admin_permission DROP parentId');
    }
}
