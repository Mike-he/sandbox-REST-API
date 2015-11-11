<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151111103050_feature_13568 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("
          CREATE TABLE `EventDate` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `eventId` int(11) NOT NULL,
              `date` date NOT NULL,
              PRIMARY KEY (`id`),
              KEY `fk_EventDate_eventId_idx` (`eventId`),
              CONSTRAINT `fk_EventDate_eventId` FOREIGN KEY (`eventId`) REFERENCES `Event` (`id`) ON DELETE CASCADE
            )
        ");
        $this->addSql("
          CREATE TABLE `EventTime` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `dateId` int(11) NOT NULL,
              `startTime` datetime NOT NULL,
              `endTime` datetime NOT NULL,
              `description` varchar(255) NOT NULL,
              PRIMARY KEY (`id`),
              KEY `fk_EventTime_dateId_idx` (`dateId`),
              CONSTRAINT `fk_EventTime_dateId` FOREIGN KEY (`dateId`) REFERENCES `EventDate` (`id`) ON DELETE CASCADE
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

        $this->addSql("
          CREATE TABLE `EventDate` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `eventId` int(11) NOT NULL,
              `date` date NOT NULL,
              PRIMARY KEY (`id`),
              KEY `fk_EventDate_eventId_idx` (`eventId`),
              CONSTRAINT `fk_EventDate_eventId` FOREIGN KEY (`eventId`) REFERENCES `Event` (`id`) ON DELETE CASCADE
            )
        ");
        $this->addSql("
          CREATE TABLE `EventTime` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `dateId` int(11) NOT NULL,
              `startTime` datetime NOT NULL,
              `endTime` datetime NOT NULL,
              `description` varchar(255) NOT NULL,
              PRIMARY KEY (`id`),
              KEY `fk_EventTime_dateId_idx` (`dateId`),
              CONSTRAINT `fk_EventTime_dateId` FOREIGN KEY (`dateId`) REFERENCES `EventDate` (`id`) ON DELETE CASCADE
            )
        ");
    }
}
