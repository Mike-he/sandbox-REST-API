<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160919172951 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("UPDATE app_info SET `app`='sandbox' WHERE `id`>0");
        $this->addSql("INSERT INTO app_info(`platform`,`version`,`url`,`environment`,`copyrightYear`,`app`,`date`) VALUES('ios','2.2.7','http://fir.im/ah4r','production','2016','xiehe','2016-9-19');");
        $this->addSql("INSERT INTO app_info(`platform`,`version`,`url`,`environment`,`copyrightYear`,`app`,`date`) VALUES('android','2.2.7','http://download.sandbox3.cn/xiehe.apk','production','2016','xiehe','2016-9-19');");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
