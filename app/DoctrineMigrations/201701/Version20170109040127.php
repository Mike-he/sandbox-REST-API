<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170109040127 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE admin_exclude_permission ADD groupId INT DEFAULT NULL, CHANGE permissionId permissionId INT DEFAULT NULL');
        $this->addSql('ALTER TABLE admin_exclude_permission ADD CONSTRAINT FK_D18B3F8EED8188B0 FOREIGN KEY (groupId) REFERENCES admin_permission_groups (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_D18B3F8EED8188B0 ON admin_exclude_permission (groupId)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE admin_exclude_permission DROP FOREIGN KEY FK_D18B3F8EED8188B0');
        $this->addSql('DROP INDEX IDX_D18B3F8EED8188B0 ON admin_exclude_permission');
        $this->addSql('ALTER TABLE admin_exclude_permission DROP groupId, CHANGE permissionId permissionId INT NOT NULL');
    }
}
