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
        $this->addSql('ALTER TABLE CompanyIndustry ADD `key` VARCHAR(64) NOT NULL');
        $this->addSql("UPDATE UserHobby SET `key` = 'sports' WHERE `name` = '运动'");
        $this->addSql("UPDATE UserHobby SET `key` = 'chess' WHERE `name` = '棋类'");
        $this->addSql("UPDATE UserHobby SET `key` = 'tourism' WHERE `name` = '旅游'");
        $this->addSql("UPDATE UserHobby SET `key` = 'mountaineering' WHERE `name` = '登山运动'");
        $this->addSql("UPDATE UserHobby SET `key` = 'musical_instruments' WHERE `name` = '乐器'");
        $this->addSql("UPDATE UserHobby SET `key` = 'music' WHERE `name` = '音乐'");
        $this->addSql("UPDATE UserHobby SET `key` = 'dancing' WHERE `name` = '舞蹈'");
        $this->addSql("UPDATE UserHobby SET `key` = 'tea' WHERE `name` = '饮茶'");
        $this->addSql("UPDATE UserHobby SET `key` = 'movie' WHERE `name` = '影视'");
        $this->addSql("UPDATE UserHobby SET `key` = 'reading' WHERE `name` = '阅读'");
        $this->addSql("UPDATE UserHobby SET `key` = 'social_activities' WHERE `name` = '社交'");
        $this->addSql("UPDATE CompanyIndustry SET `key` = 'information_technology' WHERE `name` = 'IT'");
        $this->addSql("UPDATE CompanyIndustry SET `key` = 'communication' WHERE `name` = '通信'");
        $this->addSql("UPDATE CompanyIndustry SET `key` = 'electronic' WHERE `name` = '电子'");
        $this->addSql("UPDATE CompanyIndustry SET `key` = 'internet' WHERE `name` = '互联网'");
        $this->addSql("UPDATE CompanyIndustry SET `key` = 'financial' WHERE `name` = '金融业'");
        $this->addSql("UPDATE CompanyIndustry SET `key` = 'estate' WHERE `name` = '房地产'");
        $this->addSql("UPDATE CompanyIndustry SET `key` = 'architecture' WHERE `name` = '建筑业'");
        $this->addSql("UPDATE CompanyIndustry SET `key` = 'business_services' WHERE `name` = '商业服务'");
        $this->addSql("UPDATE CompanyIndustry SET `key` = 'trading' WHERE `name` = '贸易'");
        $this->addSql("UPDATE CompanyIndustry SET `key` = 'wholesale' WHERE `name` = '批发'");
        $this->addSql("UPDATE CompanyIndustry SET `key` = 'retail' WHERE `name` = '零售'");
        $this->addSql("UPDATE CompanyIndustry SET `key` = 'leasing' WHERE `name` = '租赁业'");
        $this->addSql("UPDATE CompanyIndustry SET `key` = 'stylistic_education' WHERE `name` = '文体教育'");
        $this->addSql("UPDATE CompanyIndustry SET `key` = 'craft_art' WHERE `name` = '工艺美术'");
        $this->addSql("UPDATE CompanyIndustry SET `key` = 'instrumentation_and_industrial_automation' WHERE `name` = '仪器仪表及工业自动化'");
        $this->addSql("UPDATE CompanyIndustry SET `key` = 'traffic' WHERE `name` = '交通'");
        $this->addSql("UPDATE CompanyIndustry SET `key` = 'transport' WHERE `name` = '运输'");
        $this->addSql("UPDATE CompanyIndustry SET `key` = 'logistics' WHERE `name` = '物流'");
        $this->addSql("UPDATE CompanyIndustry SET `key` = 'warehousing' WHERE `name` = '仓储'");
        $this->addSql("UPDATE CompanyIndustry SET `key` = 'services' WHERE `name` = '服务业'");
        $this->addSql("UPDATE CompanyIndustry SET `key` = 'culture' WHERE `name` = '文化'");
        $this->addSql("UPDATE CompanyIndustry SET `key` = 'media' WHERE `name` = '传媒'");
        $this->addSql("UPDATE CompanyIndustry SET `key` = 'entertainment' WHERE `name` = '娱乐'");
        $this->addSql("UPDATE CompanyIndustry SET `key` = 'sports' WHERE `name` = '体育'");
        $this->addSql("UPDATE CompanyIndustry SET `key` = 'energy' WHERE `name` = '能源'");
        $this->addSql("UPDATE CompanyIndustry SET `key` = 'mineral' WHERE `name` = '矿产'");
        $this->addSql("UPDATE CompanyIndustry SET `key` = 'environment_protection' WHERE `name` = '环保'");
        $this->addSql("UPDATE CompanyIndustry SET `key` = 'government' WHERE `name` = '政府'");
        $this->addSql("UPDATE CompanyIndustry SET `key` = 'non_profit_organizations' WHERE `name` = '非营利机构'");
        $this->addSql("UPDATE CompanyIndustry SET `key` = 'agriculture' WHERE `name` = '农业'");
        $this->addSql("UPDATE CompanyIndustry SET `key` = 'forestry' WHERE `name` = '林业'");
        $this->addSql("UPDATE CompanyIndustry SET `key` = 'animal_husbandry' WHERE `name` = '牧业'");
        $this->addSql("UPDATE CompanyIndustry SET `key` = 'fisheries' WHERE `name` = '渔业'");
        $this->addSql("UPDATE CompanyIndustry SET `key` = 'other' WHERE `name` = '其他'");
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
