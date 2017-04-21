<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161118105827 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("UPDATE `admin_permission` SET `name`='社区新增' WHERE `key`='sales.platform.building'");
        $this->addSql("UPDATE `admin_permission` SET `name`='空间管理总权限' WHERE `key`='sales.building.space'");
        $this->addSql("UPDATE `admin_permission` SET `name`='空间管理' WHERE `key`='platform.space'");
        $this->addSql("UPDATE `admin_permission` SET `name`='社区设置' WHERE `key`='sales.building.building'");
        $this->addSql("UPDATE `admin_permission` SET `name`='空间设置' WHERE `key`='sales.building.room'");
        $this->addSql("UPDATE `admin_permission` SET `name`='租赁设置' WHERE `key`='sales.building.product'");
        $this->addSql("UPDATE `admin_permission` SET `name`='空间预定' WHERE `key`='sales.building.order.preorder' OR `key`='platform.order.preorder'");
        $this->addSql("UPDATE `admin_permission` SET `name`='空间预留' WHERE `key`='sales.building.order.reserve' OR `key`='platform.order.reserve'");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
