<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161202021856 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs

        $this->addSql("UPDATE `room_types` SET `homepageIcon`='https://image.sandbox3.cn/icon/room_type_office_homepage.png' WHERE `name`='office'");
        $this->addSql("UPDATE `room_types` SET `homepageIcon`='https://image.sandbox3.cn/icon/room_type_meeting_homepage.png' WHERE `name`='meeting'");
        $this->addSql("UPDATE `room_types` SET `homepageIcon`='https://image.sandbox3.cn/icon/room_type_flexible_homepage.png' WHERE `name`='flexible'");
        $this->addSql("UPDATE `room_types` SET `homepageIcon`='https://image.sandbox3.cn/icon/room_type_fixed_homepage.png' WHERE `name`='fixed'");
        $this->addSql("UPDATE `room_types` SET `homepageIcon`='https://image.sandbox3.cn/icon/room_type_studio_homepage.png' WHERE `name`='studio'");
        $this->addSql("UPDATE `room_types` SET `homepageIcon`='https://image.sandbox3.cn/icon/room_type_space_homepage.png' WHERE `name`='space'");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
