<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160920103531 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX userId_positionId_UNIQUE ON admin_position_user_binding');
        $this->addSql('CREATE INDEX fk_AdminPositionUserBinding_building_idx ON admin_position_user_binding (buildingId)');
        $this->addSql('CREATE UNIQUE INDEX userId_positionId_buildingId_UNIQUE ON admin_position_user_binding (userId, positionId, buildingId)');
        $this->addSql('ALTER TABLE admin_position_user_binding ADD shopId INT DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX fk_AdminPositionUserBinding_building_idx ON admin_position_user_binding');
        $this->addSql('DROP INDEX userId_positionId_buildingId_UNIQUE ON admin_position_user_binding');
        $this->addSql('CREATE UNIQUE INDEX userId_positionId_UNIQUE ON admin_position_user_binding (userId, positionId)');
        $this->addSql('ALTER TABLE admin_position_user_binding DROP shopId');
    }
}
