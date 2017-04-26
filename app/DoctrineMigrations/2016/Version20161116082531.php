<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161116082531 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("INSERT INTO `admin_permission`(`key`,`name`,`platform`,`level`,`opLevelSelect`,`maxOpLevel`,`creationDate`,`modificationDate`) VALUES ('platform.space','空间管理','official','global','1,2','2','2016-11-16 01:01:01','2016-11-16 01:01:01')");
        $this->addSql("INSERT INTO `admin_permission`(`key`,`name`,`platform`,`level`,`opLevelSelect`,`maxOpLevel`,`creationDate`,`modificationDate`) VALUES ('sales.building.space','空间管理','sales','specify','1,2','2','2016-11-16 01:01:01','2016-11-16 01:01:01')");
        $this->addSql("UPDATE `admin_permission` SET `opLevelSelect`='2' WHERE `key` IN ('platform.order.reserve','platform.order.preorder','sales.building.order.reserve','sales.building.order.preorder')");
        $this->addSql("INSERT INTO `admin_exclude_permission`(`permissionId`,`platform`,`creationDate`) SELECT `p`.`id`,`p`.`platform`,`p`.`creationDate` FROM `admin_permission` AS `p` WHERE `p`.`key`='platform.product'");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
