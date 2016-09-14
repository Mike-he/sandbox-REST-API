<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160914113634 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("
            UPDATE `admin_permission` SET `platform`='official', `level` = 'global' WHERE `key` = 'platform.dashboard';
            UPDATE `admin_permission` SET `platform`='official', `level` = 'global' WHERE `key` = 'platform.order';
            UPDATE `admin_permission` SET `platform`='official', `level` = 'global' WHERE `key` = 'platform.user';
            UPDATE `admin_permission` SET `platform`='official', `level` = 'global' WHERE `key` = 'platform.admin';
            UPDATE `admin_permission` SET `platform`='official', `level` = 'global' WHERE `key` = 'platform.announcement';
            UPDATE `admin_permission` SET `platform`='official', `level` = 'global' WHERE `key` = 'platform.event';
            UPDATE `admin_permission` SET `platform`='official', `level` = 'global' WHERE `key` = 'platform.banner';
            UPDATE `admin_permission` SET `platform`='official', `level` = 'global' WHERE `key` = 'platform.news';
            UPDATE `admin_permission` SET `platform`='official', `level` = 'global' WHERE `key` = 'platform.message';
            UPDATE `admin_permission` SET `platform`='official', `level` = 'global' WHERE `key` = 'platform.verify';
            UPDATE `admin_permission` SET `platform`='official', `level` = 'global' WHERE `key` = 'platform.sales';
            UPDATE `admin_permission` SET `platform`='official', `level` = 'global' WHERE `key` = 'platform.invoice';
            UPDATE `admin_permission` SET `platform`='official', `level` = 'global' WHERE `key` = 'platform.access';
            UPDATE `admin_permission` SET `platform`='official', `level` = 'global' WHERE `key` = 'platform.room';
            UPDATE `admin_permission` SET `platform`='official', `level` = 'global' WHERE `key` = 'platform.product';
            UPDATE `admin_permission` SET `platform`='official', `level` = 'global' WHERE `key` = 'platform.price';
            UPDATE `admin_permission` SET `platform`='official', `level` = 'global' WHERE `key` = 'platform.building';
            UPDATE `admin_permission` SET `platform`='official', `level` = 'global' WHERE `key` = 'platform.bulletin';
            UPDATE `admin_permission` SET `platform`='official', `level` = 'global' WHERE `key` = 'platform.order.reserve';
            UPDATE `admin_permission` SET `platform`='official', `level` = 'global' WHERE `key` = 'platform.order.preorder';
            UPDATE `admin_permission` SET `platform`='official', `level` = 'global' WHERE `key` = 'platform.product.appointment';
            UPDATE `admin_permission` SET `platform`='official', `level` = 'global' WHERE `key` = 'platform.log';
            UPDATE `admin_permission` SET `platform`='official', `level` = 'global' WHERE `key` = 'platform.advertising';
        ");

        $this->addSql("
            INSERT INTO admin_permission(`key`,`name`,`platform`,`level`,`creationDate`,`modificationDate`) VALUES
                ('sales.platform.dashboard','控制台管理','sales','global','2016-03-01 00:00:00','2016-03-01 00:00:00'),
                ('sales.platform.admin','管理员管理','sales','global','2016-03-01 00:00:00','2016-03-01 00:00:00'),
                ('sales.platform.building','项目管理','sales','global','2016-03-01 00:00:00','2016-03-01 00:00:00'),
                ('sales.platform.invoice','发票管理','sales','global','2016-03-01 00:00:00','2016-03-01 00:00:00'),
                ('sales.platform.event','活动管理','sales','global','2016-03-01 00:00:00','2016-03-01 00:00:00'),
                ('sales.building.price','价格模板管理','sales','specify','2016-03-01 00:00:00','2016-03-01 00:00:00'),
                ('sales.building.order','订单管理','sales','specify','2016-03-01 00:00:00','2016-03-01 00:00:00'),
                ('sales.building.order.reserve','订单预留','sales','specify','2016-03-01 00:00:00','2016-03-01 00:00:00'),
                ('sales.building.order.preorder','订单预订','sales','specify','2016-03-01 00:00:00','2016-03-01 00:00:00'),
                ('sales.building.building','项目管理','sales','specify','2016-03-01 00:00:00','2016-03-01 00:00:00'),
                ('sales.building.user','用户管理','sales','specify','2016-03-01 00:00:00','2016-03-01 00:00:00'),
                ('sales.building.room','空间管理','sales','specify','2016-03-01 00:00:00','2016-03-01 00:00:00'),
                ('sales.building.product','商品管理','sales','specify','2016-03-01 00:00:00','2016-03-01 00:00:00'),
                ('sales.building.access','门禁管理','sales','specify','2016-03-01 00:00:00','2016-03-01 00:00:00'),
                ('shop.platform.dashboard','控制台管理','shop','global','2016-03-01 00:00:00','2016-03-01 00:00:00'),
                ('shop.platform.admin','管理员管理','shop','global','2016-03-01 00:00:00','2016-03-01 00:00:00'),
                ('shop.platform.shop','商店新增','shop','global','2016-03-01 00:00:00','2016-03-01 00:00:00'),
                ('shop.platform.spec','规格管理','shop','global','2016-03-01 00:00:00','2016-03-01 00:00:00'),
                ('shop.shop.shop','商店管理','shop','specify','2016-03-01 00:00:00','2016-03-01 00:00:00'),
                ('shop.shop.order','订单管理','shop','specify','2016-03-01 00:00:00','2016-03-01 00:00:00'),
                ('shop.shop.product','商品管理','shop','specify','2016-03-01 00:00:00','2016-03-01 00:00:00'),
                ('shop.shop.kitchen','传菜系统管理','shop','specify','2016-03-01 00:00:00','2016-03-01 00:00:00');
        ");

        $this->addSql("
              INSERT INTO `parameter` (`key`, `value`) VALUES ('banner_top', '5');
        ");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
