<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160503165917_15592_feature extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("
            CREATE TABLE `EventLike` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `eventId` int(11) NOT NULL,
              `authorId` int(11) NOT NULL,
              `creationDate` datetime NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `eventId_authorId_UNIQUE` (`eventId`,`authorId`),
              KEY `fk_EventLike_eventId_idx` (`eventId`),
              KEY `fk_EventLike_authorId_idx` (`authorId`),
              CONSTRAINT `fk_EventLike_eventId` FOREIGN KEY (`eventId`) REFERENCES `Event` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
              CONSTRAINT `fk_EventLike_authorId` FOREIGN KEY (`authorId`) REFERENCES `User` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
            )
        ");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE EventLike');
    }
}