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
        $this->addSql("UPDATE `room_city` SET `enName`='Beijing',`key`='BJ' WHERE `id`=4");
        $this->addSql("UPDATE `room_city` SET `enName`='Shanghai',`key`='SH' WHERE `id`=22");
        $this->addSql("UPDATE `room_city` SET `enName`='Guangzhou',`key`='GZ' WHERE `id`=41");
        $this->addSql("UPDATE `room_city` SET `enName`='Shenzhen',`key`='SZ' WHERE `id`=53");
        $this->addSql("UPDATE `room_city` SET `enName`='Xiamen',`key`='XM' WHERE `id`=61");
        $this->addSql("UPDATE `room_city` SET `enName`='Hangzhou',`key`='HZ' WHERE `id`=75");
        $this->addSql("UPDATE `room_city` SET `enName`='Chongqing',`key`='CQ' WHERE `id`=90");
        $this->addSql("UPDATE `room_city` SET `enName`='Qingdao',`key`='QD' WHERE `id`=130");
        $this->addSql("UPDATE `room_city` SET `enName`='Xi`an',`key`='XI`AN' WHERE `id`=142");
        $this->addSql("UPDATE `room_city` SET `enName`='Boston',`key`='BOS' WHERE `id`=157");
        $this->addSql("UPDATE `room_city` SET `enName`='San Francisco',`key`='SFO' WHERE `id`=159");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
