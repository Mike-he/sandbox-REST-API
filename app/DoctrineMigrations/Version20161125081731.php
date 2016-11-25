<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161125081731 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("UPDATE `room_city` SET `enName`='Beijing' WHERE `id`=4");
        $this->addSql("UPDATE `room_city` SET `enName`='Shanghai' WHERE `id`=22");
        $this->addSql("UPDATE `room_city` SET `enName`='Guangzhou' WHERE `id`=41");
        $this->addSql("UPDATE `room_city` SET `enName`='Shenzhen' WHERE `id`=53");
        $this->addSql("UPDATE `room_city` SET `enName`='Xiamen' WHERE `id`=61");
        $this->addSql("UPDATE `room_city` SET `enName`='Hangzhou' WHERE `id`=75");
        $this->addSql("UPDATE `room_city` SET `enName`='Chongqing' WHERE `id`=90");
        $this->addSql("UPDATE `room_city` SET `enName`='Qingdao' WHERE `id`=130");
        $this->addSql("UPDATE `room_city` SET `enName`='Xi`an' WHERE `id`=142");
        $this->addSql("UPDATE `room_city` SET `enName`='Boston' WHERE `id`=157");
        $this->addSql("UPDATE `room_city` SET `enName`='San Francisco' WHERE `id`=159");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
