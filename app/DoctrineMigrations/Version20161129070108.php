<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161129070108 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs

        $this->addSql("UPDATE `room_building_services` SET `selectedIcon`='https://image.sandbox3.cn/icon/building_service_free_wifi_selected.png' WHERE `key`='free_wifi'");
        $this->addSql("UPDATE `room_building_services` SET `selectedIcon`='https://image.sandbox3.cn/icon/building_service_print_device_selected.png' WHERE `key`='printing_devices'");
        $this->addSql("UPDATE `room_building_services` SET `selectedIcon`='https://image.sandbox3.cn/icon/building_service_tea_selected.png' WHERE `key`='tea'");
        $this->addSql("UPDATE `room_building_services` SET `selectedIcon`='https://image.sandbox3.cn/icon/building_service_coffee_selected.png' WHERE `key`='coffee'");
        $this->addSql("UPDATE `room_building_services` SET `selectedIcon`='https://image.sandbox3.cn/icon/building_service_enterprise_incubation_selected.png' WHERE `key`='enterprise_incubation'");
        $this->addSql("UPDATE `room_building_services` SET `selectedIcon`='https://image.sandbox3.cn/icon/building_service_register_agent_selected.png' WHERE `key`='register_agent'");
        $this->addSql("UPDATE `room_building_services` SET `selectedIcon`='https://image.sandbox3.cn/icon/building_service_cleaning_selected.png' WHERE `key`='cleaning'");
        $this->addSql("UPDATE `room_building_services` SET `selectedIcon`='https://image.sandbox3.cn/icon/building_service_telephone_booth_selected.png' WHERE `key`='telephone_booth'");
        $this->addSql("UPDATE `room_building_services` SET `selectedIcon`='https://image.sandbox3.cn/icon/building_service_meeting_service_selected.png' WHERE `key`='meeting_service'");
        $this->addSql("UPDATE `room_building_services` SET `selectedIcon`='https://image.sandbox3.cn/icon/building_service_community_manager_selected.png' WHERE `key`='community_manager'");
        $this->addSql("UPDATE `room_building_services` SET `selectedIcon`='https://image.sandbox3.cn/icon/building_service_enterprise_service_selected.png' WHERE `key`='enterprise_service'");

        $this->addSql("UPDATE `admin_position_icons` SET `selectedIcon`='/icon/admin_position_icon_01_selected.png' WHERE `id`=1");
        $this->addSql("UPDATE `admin_position_icons` SET `selectedIcon`='/icon/admin_position_icon_02_selected.png' WHERE `id`=2");
        $this->addSql("UPDATE `admin_position_icons` SET `selectedIcon`='/icon/admin_position_icon_03_selected.png' WHERE `id`=3");
        $this->addSql("UPDATE `admin_position_icons` SET `selectedIcon`='/icon/admin_position_icon_04_selected.png' WHERE `id`=4");
        $this->addSql("UPDATE `admin_position_icons` SET `selectedIcon`='/icon/admin_position_icon_05_selected.png' WHERE `id`=5");
        $this->addSql("UPDATE `admin_position_icons` SET `selectedIcon`='/icon/admin_position_icon_06_selected.png' WHERE `id`=6");
        $this->addSql("UPDATE `admin_position_icons` SET `selectedIcon`='/icon/admin_position_icon_07_selected.png' WHERE `id`=7");
        $this->addSql("UPDATE `admin_position_icons` SET `selectedIcon`='/icon/admin_position_icon_08_selected.png' WHERE `id`=8");
        $this->addSql("UPDATE `admin_position_icons` SET `selectedIcon`='/icon/admin_position_icon_09_selected.png' WHERE `id`=9");
        $this->addSql("UPDATE `admin_position_icons` SET `selectedIcon`='/icon/admin_position_icon_10_selected.png' WHERE `id`=10");
        $this->addSql("UPDATE `admin_position_icons` SET `selectedIcon`='/icon/admin_position_icon_11_selected.png' WHERE `id`=11");
        $this->addSql("UPDATE `admin_position_icons` SET `selectedIcon`='/icon/admin_position_icon_12_selected.png' WHERE `id`=12");
        $this->addSql("UPDATE `admin_position_icons` SET `selectedIcon`='/icon/admin_position_icon_13_selected.png' WHERE `id`=13");
        $this->addSql("UPDATE `admin_position_icons` SET `selectedIcon`='/icon/admin_position_icon_14_selected.png' WHERE `id`=14");
        $this->addSql("UPDATE `admin_position_icons` SET `selectedIcon`='/icon/admin_position_icon_15_selected.png' WHERE `id`=15");
        $this->addSql("UPDATE `admin_position_icons` SET `selectedIcon`='/icon/admin_position_icon_16_selected.png' WHERE `id`=16");
        $this->addSql("UPDATE `admin_position_icons` SET `selectedIcon`='/icon/admin_position_icon_17_selected.png' WHERE `id`=17");
        $this->addSql("UPDATE `admin_position_icons` SET `selectedIcon`='/icon/admin_position_icon_18_selected.png' WHERE `id`=18");
        $this->addSql("UPDATE `admin_position_icons` SET `selectedIcon`='/icon/admin_position_icon_19_selected.png' WHERE `id`=19");
        $this->addSql("UPDATE `admin_position_icons` SET `selectedIcon`='/icon/admin_position_icon_20_selected.png' WHERE `id`=20");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
