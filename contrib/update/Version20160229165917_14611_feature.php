<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160229165917_14611_feature extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE SalesAdmin (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(64) NOT NULL, password VARCHAR(256) NOT NULL, name VARCHAR(128) DEFAULT NULL, typeId INT NOT NULL, creationDate DATETIME NOT NULL, modificationDate DATETIME NOT NULL, companyId INT NOT NULL, defaultPasswordChanged TINYINT(1) NOT NULL, banned TINYINT(1) NOT NULL, INDEX IDX_A620BC6C9BF49490 (typeId), INDEX IDX_A620BC6C2480E723 (companyId), UNIQUE INDEX username_UNIQUE (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE SalesAdminPermissionMap (id INT AUTO_INCREMENT NOT NULL, adminId INT NOT NULL, permissionId INT NOT NULL, creationDate DATETIME NOT NULL, opLevel INT NOT NULL, buildingId INT DEFAULT NULL, INDEX IDX_4771ECD5605405B0 (permissionId), INDEX IDX_4771ECD52D696931 (adminId), INDEX fk_AdminPermissionMap_buildingId_idx (buildingId), UNIQUE INDEX adminId_permissionId_buildingId_UNIQUE (adminId, permissionId, buildingId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE SalesCompany (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(64) NOT NULL, applicantName VARCHAR(64) NOT NULL, phone VARCHAR(64) NOT NULL, email VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, creationDate DATETIME NOT NULL, modificationDate DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE SalesAdminClient (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(128) DEFAULT NULL, os VARCHAR(256) DEFAULT NULL, version VARCHAR(16) DEFAULT NULL, ipAddress VARCHAR(64) DEFAULT NULL, creationDate DATETIME NOT NULL, modificationDate DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE SalesAdminPermission (id INT AUTO_INCREMENT NOT NULL, `key` VARCHAR(32) NOT NULL, name VARCHAR(64) NOT NULL, typeId INT NOT NULL, creationDate DATETIME NOT NULL, modificationDate DATETIME NOT NULL, INDEX IDX_1792C20F9BF49490 (typeId), UNIQUE INDEX key_UNIQUE (`key`), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE SalesAdminToken (id INT AUTO_INCREMENT NOT NULL, adminId INT NOT NULL, clientId INT NOT NULL, token VARCHAR(64) NOT NULL, creationDate DATETIME NOT NULL, INDEX IDX_5580B8DE2D696931 (adminId), INDEX IDX_5580B8DEEA1CE9BE (clientId), UNIQUE INDEX token_UNIQUE (token), UNIQUE INDEX adminId_clientId_UNIQUE (adminId, clientId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `SalesAdminType` (`id` int(11) NOT NULL AUTO_INCREMENT,`key` varchar(32) NOT NULL,`name` varchar(64) NOT NULL,`creationDate` datetime NOT NULL,`modificationDate` datetime NOT NULL,PRIMARY KEY (`id`),UNIQUE KEY `key_UNIQUE` (`key`))');
        $this->addSql("CREATE VIEW `SalesAdminApiAuthView` AS select `t`.`id` AS `id`,`t`.`token` AS `token`,`t`.`clientId` AS `clientId`,`a`.`id` AS `adminId`,`a`.`username` AS `username` from (`SalesAdminToken` `t` join `SalesAdmin` `a` on((`t`.`adminId` = `a`.`id`))) where (`t`.`creationDate` > (now() - interval 5 day))");
        $this->addSql('ALTER TABLE SalesAdmin ADD CONSTRAINT FK_A620BC6C9BF49490 FOREIGN KEY (typeId) REFERENCES SalesAdminType (id)');
        $this->addSql('ALTER TABLE SalesAdmin ADD CONSTRAINT FK_A620BC6C2480E723 FOREIGN KEY (companyId) REFERENCES SalesCompany (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE SalesAdminPermissionMap ADD CONSTRAINT FK_4771ECD5605405B0 FOREIGN KEY (permissionId) REFERENCES SalesAdminPermission (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE SalesAdminPermissionMap ADD CONSTRAINT FK_4771ECD52D696931 FOREIGN KEY (adminId) REFERENCES SalesAdmin (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE SalesAdminPermission ADD CONSTRAINT FK_1792C20F9BF49490 FOREIGN KEY (typeId) REFERENCES SalesAdminType (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE SalesAdminToken ADD CONSTRAINT FK_5580B8DE2D696931 FOREIGN KEY (adminId) REFERENCES SalesAdmin (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE SalesAdminToken ADD CONSTRAINT FK_5580B8DEEA1CE9BE FOREIGN KEY (clientId) REFERENCES SalesAdminClient (id) ON DELETE CASCADE');
        $this->addSql("ALTER TABLE RoomBuilding ADD visible TINYINT(1) NOT NULL, ADD companyId INT NOT NULL, ADD status ENUM('pending', 'accept', 'refuse', 'banned') NOT NULL, ADD isDeleted TINYINT(1) NOT NULL");
        $this->addSql('ALTER TABLE Product ADD isDeleted TINYINT(1) NOT NULL');
        $this->addSql('INSERT INTO SalesAdminType(`key`,`name`,`creationDate`,`modificationDate`) VALUES(\'super\',\'超级管理员\',\'2016-03-01 00:00:00\',\'2016-03-01 00:00:00\')');
        $this->addSql('INSERT INTO SalesAdminType(`key`,`name`,`creationDate`,`modificationDate`) VALUES(\'platform\',\'平台管理员\',\'2016-03-01 00:00:00\',\'2016-03-01 00:00:00\')');
        $this->addSql("INSERT INTO SalesAdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'sales.platform.admin','管理员管理','2016-03-01 00:00:00','2016-03-01 00:00:00')");
        $this->addSql("INSERT INTO SalesAdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'sales.platform.building','项目管理','2016-03-01 00:00:00','2016-03-01 00:00:00')");
        $this->addSql("INSERT INTO SalesAdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'sales.building.price','价格模板管理','2016-03-01 00:00:00','2016-03-01 00:00:00')");
        $this->addSql("INSERT INTO SalesAdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'sales.building.order','订单管理','2016-03-01 00:00:00','2016-03-01 00:00:00')");
        $this->addSql("INSERT INTO SalesAdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'sales.building.building','项目管理','2016-03-01 00:00:00','2016-03-01 00:00:00')");
        $this->addSql("INSERT INTO SalesAdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'sales.building.user','用户管理','2016-03-01 00:00:00','2016-03-01 00:00:00')");
        $this->addSql("INSERT INTO SalesAdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'sales.building.room','空间管理','2016-03-01 00:00:00','2016-03-01 00:00:00')");
        $this->addSql("INSERT INTO SalesAdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'sales.building.product','商品管理','2016-03-01 00:00:00','2016-03-01 00:00:00')");
        $this->addSql("INSERT INTO SalesAdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'sales.building.access','门禁管理','2016-03-01 00:00:00','2016-03-01 00:00:00')");
        $this->addSql("INSERT INTO AdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'platform.sales','销售方管理','2016-03-01 00:00:00','2016-03-01 00:00:00')");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE SalesAdminPermissionMap DROP FOREIGN KEY FK_4771ECD52D696931');
        $this->addSql('ALTER TABLE SalesAdminToken DROP FOREIGN KEY FK_5580B8DE2D696931');
        $this->addSql('ALTER TABLE SalesAdmin DROP FOREIGN KEY FK_A620BC6C2480E723');
        $this->addSql('ALTER TABLE SalesAdminToken DROP FOREIGN KEY FK_5580B8DEEA1CE9BE');
        $this->addSql('ALTER TABLE SalesAdminPermissionMap DROP FOREIGN KEY FK_4771ECD5605405B0');
        $this->addSql('DROP TABLE SalesAdmin');
        $this->addSql('DROP TABLE SalesAdminPermissionMap');
        $this->addSql('DROP TABLE SalesCompany');
        $this->addSql('DROP TABLE SalesAdminClient');
        $this->addSql('DROP TABLE SalesAdminPermission');
        $this->addSql('DROP TABLE SalesAdminToken');
    }
}