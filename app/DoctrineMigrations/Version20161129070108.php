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

        $this->addSql("UPDATE `admin_position_icons` SET `selectedIcon`='/icon/admin_position_icon_01_selected.png'");
        $this->addSql("UPDATE `admin_position_icons` SET `selectedIcon`='/icon/admin_position_icon_02_selected.png'");
        $this->addSql("UPDATE `admin_position_icons` SET `selectedIcon`='/icon/admin_position_icon_03_selected.png'");
        $this->addSql("UPDATE `admin_position_icons` SET `selectedIcon`='/icon/admin_position_icon_04_selected.png'");
        $this->addSql("UPDATE `admin_position_icons` SET `selectedIcon`='/icon/admin_position_icon_05_selected.png'");
        $this->addSql("UPDATE `admin_position_icons` SET `selectedIcon`='/icon/admin_position_icon_06_selected.png'");
        $this->addSql("UPDATE `admin_position_icons` SET `selectedIcon`='/icon/admin_position_icon_07_selected.png'");
        $this->addSql("UPDATE `admin_position_icons` SET `selectedIcon`='/icon/admin_position_icon_08_selected.png'");
        $this->addSql("UPDATE `admin_position_icons` SET `selectedIcon`='/icon/admin_position_icon_09_selected.png'");
        $this->addSql("UPDATE `admin_position_icons` SET `selectedIcon`='/icon/admin_position_icon_10_selected.png'");
        $this->addSql("UPDATE `admin_position_icons` SET `selectedIcon`='/icon/admin_position_icon_11_selected.png'");
        $this->addSql("UPDATE `admin_position_icons` SET `selectedIcon`='/icon/admin_position_icon_12_selected.png'");
        $this->addSql("UPDATE `admin_position_icons` SET `selectedIcon`='/icon/admin_position_icon_13_selected.png'");
        $this->addSql("UPDATE `admin_position_icons` SET `selectedIcon`='/icon/admin_position_icon_14_selected.png'");
        $this->addSql("UPDATE `admin_position_icons` SET `selectedIcon`='/icon/admin_position_icon_15_selected.png'");
        $this->addSql("UPDATE `admin_position_icons` SET `selectedIcon`='/icon/admin_position_icon_16_selected.png'");
        $this->addSql("UPDATE `admin_position_icons` SET `selectedIcon`='/icon/admin_position_icon_17_selected.png'");
        $this->addSql("UPDATE `admin_position_icons` SET `selectedIcon`='/icon/admin_position_icon_18_selected.png'");
        $this->addSql("UPDATE `admin_position_icons` SET `selectedIcon`='/icon/admin_position_icon_19_selected.png'");
        $this->addSql("UPDATE `admin_position_icons` SET `selectedIcon`='/icon/admin_position_icon_20_selected.png'");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
