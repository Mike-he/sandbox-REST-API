<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160308160454_14801_feature extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE ShopAdmin (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(64) NOT NULL, password VARCHAR(256) NOT NULL, name VARCHAR(128) DEFAULT NULL, typeId INT NOT NULL, creationDate DATETIME NOT NULL, modificationDate DATETIME NOT NULL, companyId INT NOT NULL, defaultPasswordChanged TINYINT(1) NOT NULL, banned TINYINT(1) NOT NULL, INDEX IDX_57E936B19BF49490 (typeId), INDEX IDX_57E936B12480E723 (companyId), UNIQUE INDEX username_UNIQUE (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ShopAdminClient (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(128) DEFAULT NULL, os VARCHAR(256) DEFAULT NULL, version VARCHAR(16) DEFAULT NULL, ipAddress VARCHAR(64) DEFAULT NULL, creationDate DATETIME NOT NULL, modificationDate DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ShopAdminPermission (id INT AUTO_INCREMENT NOT NULL, `key` VARCHAR(32) NOT NULL, name VARCHAR(64) NOT NULL, typeId INT NOT NULL, creationDate DATETIME NOT NULL, modificationDate DATETIME NOT NULL, INDEX IDX_E9E621569BF49490 (typeId), UNIQUE INDEX key_UNIQUE (`key`), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ShopAdminPermissionMap (id INT AUTO_INCREMENT NOT NULL, adminId INT NOT NULL, permissionId INT NOT NULL, creationDate DATETIME NOT NULL, opLevel INT NOT NULL, shopId INT DEFAULT NULL, INDEX IDX_627EC1E605405B0 (permissionId), INDEX IDX_627EC1E2D696931 (adminId), INDEX fk_AdminPermissionMap_shopId_idx (shopId), UNIQUE INDEX adminId_permissionId_buildingId_UNIQUE (adminId, permissionId, shopId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ShopAdminToken (id INT AUTO_INCREMENT NOT NULL, adminId INT NOT NULL, clientId INT NOT NULL, token VARCHAR(64) NOT NULL, creationDate DATETIME NOT NULL, INDEX IDX_35D8285A2D696931 (adminId), INDEX IDX_35D8285AEA1CE9BE (clientId), UNIQUE INDEX token_UNIQUE (token), UNIQUE INDEX adminId_clientId_UNIQUE (adminId, clientId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `ShopAdminType` (`id` int(11) NOT NULL AUTO_INCREMENT,`key` varchar(32) NOT NULL,`name` varchar(64) NOT NULL,`creationDate` datetime NOT NULL,`modificationDate` datetime NOT NULL,PRIMARY KEY (`id`),UNIQUE KEY `key_UNIQUE` (`key`))');
        $this->addSql("CREATE VIEW `ShopAdminApiAuthView` AS select `t`.`id` AS `id`,`t`.`token` AS `token`,`t`.`clientId` AS `clientId`,`a`.`id` AS `adminId`,`a`.`username` AS `username` from (`ShopAdminToken` `t` join `ShopAdmin` `a` on((`t`.`adminId` = `a`.`id`))) where (`t`.`creationDate` > (now() - interval 5 day))");
        $this->addSql('ALTER TABLE ShopAdmin ADD CONSTRAINT FK_57E936B19BF49490 FOREIGN KEY (typeId) REFERENCES ShopAdminType (id)');
        $this->addSql('ALTER TABLE ShopAdmin ADD CONSTRAINT FK_57E936B12480E723 FOREIGN KEY (companyId) REFERENCES SalesCompany (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ShopAdminPermission ADD CONSTRAINT FK_E9E621569BF49490 FOREIGN KEY (typeId) REFERENCES ShopAdminType (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ShopAdminPermissionMap ADD CONSTRAINT FK_627EC1E605405B0 FOREIGN KEY (permissionId) REFERENCES ShopAdminPermission (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ShopAdminPermissionMap ADD CONSTRAINT FK_627EC1E2D696931 FOREIGN KEY (adminId) REFERENCES ShopAdmin (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ShopAdminToken ADD CONSTRAINT FK_35D8285A2D696931 FOREIGN KEY (adminId) REFERENCES ShopAdmin (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ShopAdminToken ADD CONSTRAINT FK_35D8285AEA1CE9BE FOREIGN KEY (clientId) REFERENCES ShopAdminClient (id) ON DELETE CASCADE');
        $this->addSql('INSERT INTO ShopAdminType(`key`,`name`,`creationDate`,`modificationDate`) VALUES(\'super\',\'超级管理员\',\'2016-03-01 00:00:00\',\'2016-03-01 00:00:00\')');
        $this->addSql('INSERT INTO ShopAdminType(`key`,`name`,`creationDate`,`modificationDate`) VALUES(\'platform\',\'平台管理员\',\'2016-03-01 00:00:00\',\'2016-03-01 00:00:00\')');
        $this->addSql("INSERT INTO ShopAdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'shop.platform.admin','管理员管理','2016-03-01 00:00:00','2016-03-01 00:00:00')");
        $this->addSql("INSERT INTO ShopAdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'shop.platform.shop','商店新增','2016-03-01 00:00:00','2016-03-01 00:00:00')");
        $this->addSql("INSERT INTO ShopAdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'shop.shop.shop','商店管理','2016-03-01 00:00:00','2016-03-01 00:00:00')");
        $this->addSql("INSERT INTO ShopAdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'shop.shop.order','订单管理','2016-03-01 00:00:00','2016-03-01 00:00:00')");
        $this->addSql("INSERT INTO ShopAdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'shop.shop.product','商品管理','2016-03-01 00:00:00','2016-03-01 00:00:00')");
        $this->addSql("INSERT INTO ShopAdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'shop.shop.spec','规格管理','2016-03-01 00:00:00','2016-03-01 00:00:00')");
        $this->addSql("INSERT INTO ShopAdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'shop.shop.kitchen','传菜系统管理','2016-03-01 00:00:00','2016-03-01 00:00:00')");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ShopAdminPermissionMap DROP FOREIGN KEY FK_627EC1E2D696931');
        $this->addSql('ALTER TABLE ShopAdminToken DROP FOREIGN KEY FK_35D8285A2D696931');
        $this->addSql('ALTER TABLE ShopAdminToken DROP FOREIGN KEY FK_35D8285AEA1CE9BE');
        $this->addSql('ALTER TABLE ShopAdminPermissionMap DROP FOREIGN KEY FK_627EC1E605405B0');
        $this->addSql('DROP TABLE ShopAdmin');
        $this->addSql('DROP TABLE ShopAdminClient');
        $this->addSql('DROP TABLE ShopAdminPermission');
        $this->addSql('DROP TABLE ShopAdminPermissionMap');
        $this->addSql('DROP TABLE ShopAdminToken');
    }
}
