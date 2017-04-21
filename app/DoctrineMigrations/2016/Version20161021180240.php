<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161021180240 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("INSERT INTO admin_permission(`key`,`name`,`platform`,`level`,`creationDate`,`modificationDate`, `opLevelSelect`, `maxOpLevel`) VALUES('platform.order.refund','退款','official','global','2016-10-21 00:00:00','2016-10-21 00:00:00','2',2);");
        $this->addSql("INSERT INTO admin_permission(`key`,`name`,`platform`,`level`,`creationDate`,`modificationDate`, `opLevelSelect`, `maxOpLevel`) VALUES('platform.finance','财务管理','official','global','2016-10-21 00:00:00','2016-10-21 00:00:00','1,2',2);");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
