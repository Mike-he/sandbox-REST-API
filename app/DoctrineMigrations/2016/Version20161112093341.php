<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161112093341 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE room_building_tag ADD iconWithBg LONGTEXT NOT NULL');
        $this->addSql("UPDATE `room_building_tag` SET `iconWithBg`='https://image.sandbox3.cn/icon/building_tag_sandbox_manage_with_bg.png' WHERE `key`='sandbox3_manage'");
        $this->addSql("UPDATE `room_building_tag` SET `iconWithBg`='https://image.sandbox3.cn/icon/building_tag_7*24_with_bg.png' WHERE `key`='round_the_clock_service'");
        $this->addSql("UPDATE `room_building_tag` SET `iconWithBg`='https://image.sandbox3.cn/icon/building_tag_auth_incubator_with_bg.png' WHERE `key`='certification_of_incubator'");
        $this->addSql("UPDATE `room_building_tag` SET `iconWithBg`='https://image.sandbox3.cn/icon/building_tag_food_with_bg.png' WHERE `key`='food_and_beverage'");
        $this->addSql("UPDATE `room_building_tag` SET `iconWithBg`='https://image.sandbox3.cn/icon/building_tag_vip_space_with_bg.png' WHERE `key`='vip_space'");
        $this->addSql("UPDATE `room_building_tag` SET `iconWithBg`='https://image.sandbox3.cn/icon/building_tag_fast_preorder_with_bg.png' WHERE `key`='fast_preorder'");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE room_building_tag DROP iconWithBg');
    }
}
