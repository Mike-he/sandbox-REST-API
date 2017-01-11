<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161117023642 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("UPDATE `admin_permission` SET `parentId`='48' WHERE `key` IN ('platform.order.reserve','platform.order.preorder','platform.room','platform.building','platform.product')");
        $this->addSql("UPDATE `admin_permission` SET `parentId`='49' WHERE `key` IN ('sales.building.order.reserve','sales.building.order.preorder','sales.building.room','sales.building.building','sales.building.product','sales.platform.building')");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
