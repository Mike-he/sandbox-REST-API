<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161222105833 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs

        $this->addSql('
            ALTER TABLE `buddy_request` MODIFY COLUMN `message` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL AFTER `recvUserId`;
            ALTER TABLE `user_profiles` MODIFY COLUMN `name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL AFTER `userId`;
            ALTER TABLE `event_comment` MODIFY COLUMN `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL AFTER `authorId`;
            ALTER TABLE `feed` MODIFY COLUMN `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL AFTER `id`;
            ALTER TABLE `feed_comment` MODIFY COLUMN `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL AFTER `authorId`;
            ALTER TABLE `news` MODIFY COLUMN `title` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL AFTER `id`;
            ALTER TABLE `user_profiles` MODIFY COLUMN `name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL AFTER `userId`;
        ');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
