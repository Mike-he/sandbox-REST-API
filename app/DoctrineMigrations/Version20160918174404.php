<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160918174404 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE admin_permission_map ADD positionId INT NOT NULL');
        $this->addSql('ALTER TABLE admin_permission_map ADD CONSTRAINT FK_503E06AEE4113647 FOREIGN KEY (positionId) REFERENCES admin_position (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_503E06AEE4113647 ON admin_permission_map (positionId)');
        $this->addSql('ALTER TABLE admin_position CHANGE parentPositionId parentPositionId INT DEFAULT NULL, CHANGE salesCompanyId salesCompanyId INT DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE admin_permission_map DROP FOREIGN KEY FK_503E06AEE4113647');
        $this->addSql('DROP INDEX IDX_503E06AEE4113647 ON admin_permission_map');
        $this->addSql('ALTER TABLE admin_permission_map DROP positionId');
        $this->addSql('ALTER TABLE admin_position CHANGE parentPositionId parentPositionId INT NOT NULL, CHANGE salesCompanyId salesCompanyId INT NOT NULL');
    }
}
