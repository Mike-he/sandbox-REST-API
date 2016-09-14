<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160914113633 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE admin_permission_map DROP FOREIGN KEY FK_503E06AE2D696931');
        $this->addSql('ALTER TABLE admin_token DROP FOREIGN KEY FK_FF1D5AC12D696931');
        $this->addSql('ALTER TABLE admin_token DROP FOREIGN KEY FK_FF1D5AC1EA1CE9BE');
        $this->addSql('ALTER TABLE admin DROP FOREIGN KEY FK_880E0D769BF49490');
        $this->addSql('ALTER TABLE admin_permission DROP FOREIGN KEY FK_2877342F9BF49490');
        $this->addSql('ALTER TABLE sales_admin_permission_map DROP FOREIGN KEY FK_93CA79DF2D696931');
        $this->addSql('ALTER TABLE sales_admin_token DROP FOREIGN KEY FK_BB164B052D696931');
        $this->addSql('ALTER TABLE sales_admin_token DROP FOREIGN KEY FK_BB164B05EA1CE9BE');
        $this->addSql('ALTER TABLE sales_admin_exclude_permission DROP FOREIGN KEY FK_2F1860A9605405B0');
        $this->addSql('ALTER TABLE sales_admin_permission_map DROP FOREIGN KEY FK_93CA79DF605405B0');
        $this->addSql('ALTER TABLE sales_admin DROP FOREIGN KEY FK_7E4ABCC09BF49490');
        $this->addSql('ALTER TABLE sales_admin_permission DROP FOREIGN KEY FK_44072F2E9BF49490');
        $this->addSql('ALTER TABLE shop_admin_permission_map DROP FOREIGN KEY FK_B3C30A932D696931');
        $this->addSql('ALTER TABLE shop_admin_token DROP FOREIGN KEY FK_2D6AACD12D696931');
        $this->addSql('ALTER TABLE shop_admin_token DROP FOREIGN KEY FK_2D6AACD1EA1CE9BE');
        $this->addSql('ALTER TABLE shop_admin_permission_map DROP FOREIGN KEY FK_B3C30A93605405B0');
        $this->addSql('ALTER TABLE shop_admin DROP FOREIGN KEY FK_4F1857249BF49490');
        $this->addSql('ALTER TABLE shop_admin_permission DROP FOREIGN KEY FK_92A339E9BF49490');
        $this->addSql('CREATE TABLE admin_building_binding (id INT AUTO_INCREMENT NOT NULL, buildingId INT NOT NULL, userId INT NOT NULL, creationDate DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE admin_exclude_permission (id INT AUTO_INCREMENT NOT NULL, salesCompanyId INT NOT NULL, permissionId INT NOT NULL, creationDate DATETIME NOT NULL, INDEX IDX_D18B3F8EC50DB8C4 (salesCompanyId), INDEX IDX_D18B3F8E605405B0 (permissionId), UNIQUE INDEX permissionId_companyId_UNIQUE (permissionId, salesCompanyId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE admin_position (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(64) NOT NULL, parentPositionId INT NOT NULL, platform VARCHAR(64) NOT NULL, salesCompanyId INT NOT NULL, isHidden TINYINT(1) NOT NULL, isDeleted TINYINT(1) NOT NULL, isSuperAdmin TINYINT(1) NOT NULL, iconId INT NOT NULL, creationDate DATETIME NOT NULL, modificationDate DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE admin_position_icons (id INT AUTO_INCREMENT NOT NULL, icon VARCHAR(1024) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE admin_position_permission_map (id INT AUTO_INCREMENT NOT NULL, positionId INT NOT NULL, permissionId INT NOT NULL, opLevel INT NOT NULL, creationDate DATETIME NOT NULL, INDEX IDX_FF715FE4E4113647 (positionId), INDEX IDX_FF715FE4605405B0 (permissionId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE admin_position_user_binding (id INT AUTO_INCREMENT NOT NULL, userId INT NOT NULL, positionId INT NOT NULL, creationDate DATETIME NOT NULL, INDEX IDX_EF4057EF64B64DCC (userId), INDEX IDX_EF4057EFE4113647 (positionId), UNIQUE INDEX userId_positionId_UNIQUE (userId, positionId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE parameter (id INT AUTO_INCREMENT NOT NULL, `key` VARCHAR(64) NOT NULL, value VARCHAR(128) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE admin_exclude_permission ADD CONSTRAINT FK_D18B3F8EC50DB8C4 FOREIGN KEY (salesCompanyId) REFERENCES sales_company (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE admin_exclude_permission ADD CONSTRAINT FK_D18B3F8E605405B0 FOREIGN KEY (permissionId) REFERENCES admin_permission (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE admin_position_permission_map ADD CONSTRAINT FK_FF715FE4E4113647 FOREIGN KEY (positionId) REFERENCES admin_position (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE admin_position_permission_map ADD CONSTRAINT FK_FF715FE4605405B0 FOREIGN KEY (permissionId) REFERENCES admin_permission (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE admin_position_user_binding ADD CONSTRAINT FK_EF4057EF64B64DCC FOREIGN KEY (userId) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE admin_position_user_binding ADD CONSTRAINT FK_EF4057EFE4113647 FOREIGN KEY (positionId) REFERENCES admin_position (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE admin');
        $this->addSql('DROP TABLE admin_client');
        $this->addSql('DROP TABLE admin_token');
        $this->addSql('DROP TABLE admin_types');
        $this->addSql('DROP TABLE sales_admin');
        $this->addSql('DROP TABLE sales_admin_client');
        $this->addSql('DROP TABLE sales_admin_exclude_permission');
        $this->addSql('DROP TABLE sales_admin_permission');
        $this->addSql('DROP TABLE sales_admin_permission_map');
        $this->addSql('DROP TABLE sales_admin_token');
        $this->addSql('DROP TABLE sales_admin_types');
        $this->addSql('DROP TABLE shop_admin');
        $this->addSql('DROP TABLE shop_admin_client');
        $this->addSql('DROP TABLE shop_admin_permission');
        $this->addSql('DROP TABLE shop_admin_permission_map');
        $this->addSql('DROP TABLE shop_admin_token');
        $this->addSql('DROP TABLE shop_admin_types');
        $this->addSql('DROP INDEX IDX_2877342F9BF49490 ON admin_permission');
        $this->addSql('ALTER TABLE admin_permission ADD platform VARCHAR(64) NOT NULL, ADD level VARCHAR(64) NOT NULL, DROP typeId');
        $this->addSql('DROP INDEX IDX_503E06AE2D696931 ON admin_permission_map');
        $this->addSql('DROP INDEX adminId_permissionId_UNIQUE ON admin_permission_map');
        $this->addSql('ALTER TABLE admin_permission_map ADD buildingId INT DEFAULT NULL, CHANGE adminid userId INT NOT NULL');
        $this->addSql('ALTER TABLE admin_permission_map ADD CONSTRAINT FK_503E06AE64B64DCC FOREIGN KEY (userId) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_503E06AE64B64DCC ON admin_permission_map (userId)');
        $this->addSql('CREATE INDEX fk_AdminPermissionMap_buildingId_idx ON admin_permission_map (buildingId)');
        $this->addSql('CREATE UNIQUE INDEX adminId_permissionId_UNIQUE ON admin_permission_map (userId, permissionId, buildingId)');
        $this->addSql('DROP VIEW IF EXISTS `admin_api_auth_view`');
        $this->addSql('DROP VIEW IF EXISTS `sales_admin_api_auth_view`');
        $this->addSql('DROP VIEW IF EXISTS `shop_admin_api_auth_view`');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE admin_position_permission_map DROP FOREIGN KEY FK_FF715FE4E4113647');
        $this->addSql('ALTER TABLE admin_position_user_binding DROP FOREIGN KEY FK_EF4057EFE4113647');
        $this->addSql('CREATE TABLE admin (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(64) NOT NULL, password VARCHAR(256) NOT NULL, name VARCHAR(128) DEFAULT NULL, typeId INT NOT NULL, creationDate DATETIME NOT NULL, modificationDate DATETIME NOT NULL, UNIQUE INDEX username_UNIQUE (username), INDEX IDX_880E0D769BF49490 (typeId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE admin_client (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(128) DEFAULT NULL, os VARCHAR(256) DEFAULT NULL, version VARCHAR(16) DEFAULT NULL, ipAddress VARCHAR(64) DEFAULT NULL, creationDate DATETIME NOT NULL, modificationDate DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE admin_token (id INT AUTO_INCREMENT NOT NULL, adminId INT NOT NULL, clientId INT NOT NULL, token VARCHAR(64) NOT NULL, creationDate DATETIME NOT NULL, UNIQUE INDEX token_UNIQUE (token), UNIQUE INDEX adminId_clientId_UNIQUE (adminId, clientId), INDEX IDX_FF1D5AC12D696931 (adminId), INDEX IDX_FF1D5AC1EA1CE9BE (clientId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE admin_types (id INT AUTO_INCREMENT NOT NULL, `key` VARCHAR(32) DEFAULT NULL, name VARCHAR(64) NOT NULL, creationDate DATETIME NOT NULL, modificationDate DATETIME NOT NULL, UNIQUE INDEX key_UNIQUE (`key`), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sales_admin (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(64) NOT NULL, password VARCHAR(256) NOT NULL, name VARCHAR(128) DEFAULT NULL, typeId INT NOT NULL, creationDate DATETIME NOT NULL, modificationDate DATETIME NOT NULL, companyId INT NOT NULL, defaultPasswordChanged TINYINT(1) NOT NULL, banned TINYINT(1) NOT NULL, UNIQUE INDEX username_UNIQUE (username), INDEX IDX_7E4ABCC09BF49490 (typeId), INDEX IDX_7E4ABCC02480E723 (companyId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sales_admin_client (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(128) DEFAULT NULL, os VARCHAR(256) DEFAULT NULL, version VARCHAR(16) DEFAULT NULL, ipAddress VARCHAR(64) DEFAULT NULL, creationDate DATETIME NOT NULL, modificationDate DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sales_admin_exclude_permission (id INT AUTO_INCREMENT NOT NULL, salesCompanyId INT NOT NULL, permissionId INT NOT NULL, UNIQUE INDEX permissionId_companyId_UNIQUE (permissionId, salesCompanyId), INDEX IDX_2F1860A9C50DB8C4 (salesCompanyId), INDEX IDX_2F1860A9605405B0 (permissionId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sales_admin_permission (id INT AUTO_INCREMENT NOT NULL, `key` VARCHAR(128) NOT NULL, name VARCHAR(64) NOT NULL, typeId INT NOT NULL, creationDate DATETIME NOT NULL, modificationDate DATETIME NOT NULL, UNIQUE INDEX key_UNIQUE (`key`), INDEX IDX_44072F2E9BF49490 (typeId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sales_admin_permission_map (id INT AUTO_INCREMENT NOT NULL, adminId INT NOT NULL, permissionId INT NOT NULL, creationDate DATETIME NOT NULL, opLevel INT NOT NULL, buildingId INT DEFAULT NULL, UNIQUE INDEX adminId_permissionId_buildingId_UNIQUE (adminId, permissionId, buildingId), INDEX IDX_93CA79DF605405B0 (permissionId), INDEX IDX_93CA79DF2D696931 (adminId), INDEX fk_AdminPermissionMap_buildingId_idx (buildingId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sales_admin_token (id INT AUTO_INCREMENT NOT NULL, adminId INT NOT NULL, clientId INT NOT NULL, token VARCHAR(64) NOT NULL, creationDate DATETIME NOT NULL, UNIQUE INDEX token_UNIQUE (token), UNIQUE INDEX adminId_clientId_UNIQUE (adminId, clientId), INDEX IDX_BB164B052D696931 (adminId), INDEX IDX_BB164B05EA1CE9BE (clientId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sales_admin_types (id INT AUTO_INCREMENT NOT NULL, `key` VARCHAR(32) DEFAULT NULL, name VARCHAR(64) NOT NULL, creationDate DATETIME NOT NULL, modificationDate DATETIME NOT NULL, UNIQUE INDEX key_UNIQUE (`key`), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE shop_admin (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(64) NOT NULL, password VARCHAR(256) NOT NULL, name VARCHAR(128) DEFAULT NULL, typeId INT NOT NULL, creationDate DATETIME NOT NULL, modificationDate DATETIME NOT NULL, companyId INT NOT NULL, defaultPasswordChanged TINYINT(1) NOT NULL, banned TINYINT(1) NOT NULL, UNIQUE INDEX username_UNIQUE (username), INDEX IDX_4F1857249BF49490 (typeId), INDEX IDX_4F1857242480E723 (companyId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE shop_admin_client (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(128) DEFAULT NULL, os VARCHAR(256) DEFAULT NULL, version VARCHAR(16) DEFAULT NULL, ipAddress VARCHAR(64) DEFAULT NULL, creationDate DATETIME NOT NULL, modificationDate DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE shop_admin_permission (id INT AUTO_INCREMENT NOT NULL, `key` VARCHAR(128) NOT NULL, name VARCHAR(64) NOT NULL, typeId INT NOT NULL, creationDate DATETIME NOT NULL, modificationDate DATETIME NOT NULL, UNIQUE INDEX key_UNIQUE (`key`), INDEX IDX_92A339E9BF49490 (typeId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE shop_admin_permission_map (id INT AUTO_INCREMENT NOT NULL, adminId INT NOT NULL, permissionId INT NOT NULL, creationDate DATETIME NOT NULL, opLevel INT NOT NULL, shopId INT DEFAULT NULL, UNIQUE INDEX adminId_permissionId_shopId_UNIQUE (adminId, permissionId, shopId), INDEX IDX_B3C30A93605405B0 (permissionId), INDEX IDX_B3C30A932D696931 (adminId), INDEX fk_AdminPermissionMap_shopId_idx (shopId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE shop_admin_token (id INT AUTO_INCREMENT NOT NULL, adminId INT NOT NULL, clientId INT NOT NULL, token VARCHAR(64) NOT NULL, creationDate DATETIME NOT NULL, UNIQUE INDEX token_UNIQUE (token), UNIQUE INDEX adminId_clientId_UNIQUE (adminId, clientId), INDEX IDX_2D6AACD12D696931 (adminId), INDEX IDX_2D6AACD1EA1CE9BE (clientId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE shop_admin_types (id INT AUTO_INCREMENT NOT NULL, `key` VARCHAR(32) DEFAULT NULL, name VARCHAR(64) NOT NULL, creationDate DATETIME NOT NULL, modificationDate DATETIME NOT NULL, UNIQUE INDEX key_UNIQUE (`key`), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE admin ADD CONSTRAINT FK_880E0D769BF49490 FOREIGN KEY (typeId) REFERENCES admin_types (id)');
        $this->addSql('ALTER TABLE admin_token ADD CONSTRAINT FK_FF1D5AC12D696931 FOREIGN KEY (adminId) REFERENCES admin (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE admin_token ADD CONSTRAINT FK_FF1D5AC1EA1CE9BE FOREIGN KEY (clientId) REFERENCES admin_client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sales_admin ADD CONSTRAINT FK_7E4ABCC02480E723 FOREIGN KEY (companyId) REFERENCES sales_company (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sales_admin ADD CONSTRAINT FK_7E4ABCC09BF49490 FOREIGN KEY (typeId) REFERENCES sales_admin_types (id)');
        $this->addSql('ALTER TABLE sales_admin_exclude_permission ADD CONSTRAINT FK_2F1860A9605405B0 FOREIGN KEY (permissionId) REFERENCES sales_admin_permission (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sales_admin_exclude_permission ADD CONSTRAINT FK_2F1860A9C50DB8C4 FOREIGN KEY (salesCompanyId) REFERENCES sales_company (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sales_admin_permission ADD CONSTRAINT FK_44072F2E9BF49490 FOREIGN KEY (typeId) REFERENCES sales_admin_types (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sales_admin_permission_map ADD CONSTRAINT FK_93CA79DF2D696931 FOREIGN KEY (adminId) REFERENCES sales_admin (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sales_admin_permission_map ADD CONSTRAINT FK_93CA79DF605405B0 FOREIGN KEY (permissionId) REFERENCES sales_admin_permission (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sales_admin_token ADD CONSTRAINT FK_BB164B052D696931 FOREIGN KEY (adminId) REFERENCES sales_admin (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sales_admin_token ADD CONSTRAINT FK_BB164B05EA1CE9BE FOREIGN KEY (clientId) REFERENCES sales_admin_client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE shop_admin ADD CONSTRAINT FK_4F1857242480E723 FOREIGN KEY (companyId) REFERENCES sales_company (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE shop_admin ADD CONSTRAINT FK_4F1857249BF49490 FOREIGN KEY (typeId) REFERENCES shop_admin_types (id)');
        $this->addSql('ALTER TABLE shop_admin_permission ADD CONSTRAINT FK_92A339E9BF49490 FOREIGN KEY (typeId) REFERENCES shop_admin_types (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE shop_admin_permission_map ADD CONSTRAINT FK_B3C30A932D696931 FOREIGN KEY (adminId) REFERENCES shop_admin (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE shop_admin_permission_map ADD CONSTRAINT FK_B3C30A93605405B0 FOREIGN KEY (permissionId) REFERENCES shop_admin_permission (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE shop_admin_token ADD CONSTRAINT FK_2D6AACD12D696931 FOREIGN KEY (adminId) REFERENCES shop_admin (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE shop_admin_token ADD CONSTRAINT FK_2D6AACD1EA1CE9BE FOREIGN KEY (clientId) REFERENCES shop_admin_client (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE admin_building_binding');
        $this->addSql('DROP TABLE admin_exclude_permission');
        $this->addSql('DROP TABLE admin_position');
        $this->addSql('DROP TABLE admin_position_icons');
        $this->addSql('DROP TABLE admin_position_permission_map');
        $this->addSql('DROP TABLE admin_position_user_binding');
        $this->addSql('DROP TABLE parameter');
        $this->addSql('ALTER TABLE admin_permission ADD typeId INT NOT NULL, DROP platform, DROP level');
        $this->addSql('ALTER TABLE admin_permission ADD CONSTRAINT FK_2877342F9BF49490 FOREIGN KEY (typeId) REFERENCES admin_types (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_2877342F9BF49490 ON admin_permission (typeId)');
        $this->addSql('ALTER TABLE admin_permission_map DROP FOREIGN KEY FK_503E06AE64B64DCC');
        $this->addSql('DROP INDEX IDX_503E06AE64B64DCC ON admin_permission_map');
        $this->addSql('DROP INDEX fk_AdminPermissionMap_buildingId_idx ON admin_permission_map');
        $this->addSql('DROP INDEX adminId_permissionId_UNIQUE ON admin_permission_map');
        $this->addSql('ALTER TABLE admin_permission_map DROP buildingId, CHANGE userid adminId INT NOT NULL');
        $this->addSql('ALTER TABLE admin_permission_map ADD CONSTRAINT FK_503E06AE2D696931 FOREIGN KEY (adminId) REFERENCES admin (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_503E06AE2D696931 ON admin_permission_map (adminId)');
        $this->addSql('CREATE UNIQUE INDEX adminId_permissionId_UNIQUE ON admin_permission_map (adminId, permissionId)');
    }
}
