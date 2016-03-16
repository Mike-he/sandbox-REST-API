<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160316112956_14885_feature extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE UserHobby ADD `key` VARCHAR(64) NOT NULL');
        $this->addSql("UPDATE UserHobby SET `key` = 'sports' WHERE `name` = '运动'");
        $this->addSql("UPDATE UserHobby SET `key` = 'chess' WHERE `name` = '棋类'");
        $this->addSql("UPDATE UserHobby SET `key` = 'tourism' WHERE `name` = '旅游'");
        $this->addSql("UPDATE UserHobby SET `key` = 'mountaineering' WHERE `name` = '登山运动'");
        $this->addSql("UPDATE UserHobby SET `key` = 'musical_instruments' WHERE `name` = '乐器'");
        $this->addSql("UPDATE UserHobby SET `key` = 'dancing' WHERE `name` = '舞蹈'");
        $this->addSql("UPDATE UserHobby SET `key` = 'tea' WHERE `name` = '饮茶'");
        $this->addSql("UPDATE UserHobby SET `key` = 'movie' WHERE `name` = '影视'");
        $this->addSql("UPDATE UserHobby SET `key` = 'reading' WHERE `name` = '阅读'");
        $this->addSql("UPDATE UserHobby SET `key` = 'social_activities' WHERE `name` = '社交'");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE UserHobby DROP `key`');
    }
}
