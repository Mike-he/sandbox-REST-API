<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160919103423 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE admin_permission_map');
        $this->addSql('ALTER TABLE admin_position_user_binding ADD buildingId INT DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE admin_permission_map (id INT AUTO_INCREMENT NOT NULL, userId INT NOT NULL, permissionId INT NOT NULL, creationDate DATETIME NOT NULL, opLevel INT NOT NULL, buildingId INT DEFAULT NULL, positionId INT NOT NULL, UNIQUE INDEX adminId_permissionId_UNIQUE (userId, permissionId, buildingId), INDEX IDX_503E06AE605405B0 (permissionId), INDEX IDX_503E06AE64B64DCC (userId), INDEX fk_AdminPermissionMap_buildingId_idx (buildingId), INDEX IDX_503E06AEE4113647 (positionId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE admin_permission_map ADD CONSTRAINT FK_503E06AE605405B0 FOREIGN KEY (permissionId) REFERENCES admin_permission (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE admin_permission_map ADD CONSTRAINT FK_503E06AE64B64DCC FOREIGN KEY (userId) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE admin_permission_map ADD CONSTRAINT FK_503E06AEE4113647 FOREIGN KEY (positionId) REFERENCES admin_position (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE admin_position_user_binding DROP buildingId');
    }
}
