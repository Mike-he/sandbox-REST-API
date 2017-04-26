<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161111105621 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("
            UPDATE `admin_permission` SET `platform`='official', `level` = 'global', `opLevelSelect` = '1,2', `maxOpLevel` = '2' WHERE `key` = 'platform.log';
        ");
        $this->addSql('UPDATE top_up_order SET paymentDate = top_up_order.creationDate WHERE id>0;');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("
            UPDATE `admin_permission` SET `platform`='official', `level` = 'global', `opLevelSelect` = '1', `maxOpLevel` = '1' WHERE `key` = 'platform.log';
        ");
    }
}
