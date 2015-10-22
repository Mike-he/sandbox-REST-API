<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151015173807_feature_13263 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("CREATE TABLE Event (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, cityId INT NOT NULL, buildingId INT NOT NULL, roomId INT DEFAULT NULL, limitNumber INT NOT NULL, registrationStartDate DATETIME NOT NULL, registrationEndDate DATETIME NOT NULL, registrationMethod ENUM('online', 'offline') NOT NULL, verify TINYINT(1) NOT NULL, visible TINYINT(1) NOT NULL, eventEndDate DATETIME NOT NULL, creationDate DATETIME NOT NULL, modificationDate DATETIME NOT NULL, PRIMARY KEY(id))");
        $this->addSql('CREATE TABLE EventAttachment (id INT AUTO_INCREMENT NOT NULL, eventId INT NOT NULL, content LONGTEXT NOT NULL, attachmentType VARCHAR(64) NOT NULL, filename VARCHAR(255) NOT NULL, preview LONGTEXT NOT NULL, size INT NOT NULL, INDEX IDX_75CE7BD52B2EBB6C (eventId), PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE EventDate (id INT AUTO_INCREMENT NOT NULL, eventId INT NOT NULL, date DATE NOT NULL, PRIMARY KEY(id));');
        $this->addSql('CREATE TABLE EventTime (id INT AUTO_INCREMENT NOT NULL, dateId INT NOT NULL, startTime DATETIME NOT NULL, endTime DATETIME NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(id));');
        $this->addSql("CREATE TABLE EventForm (id INT AUTO_INCREMENT NOT NULL, eventId INT NOT NULL, title VARCHAR(255) NOT NULL, type ENUM('text', 'email', 'phone', 'radio', 'checkbox') NOT NULL, PRIMARY KEY(id))");
        $this->addSql('CREATE TABLE EventFormOption (id INT AUTO_INCREMENT NOT NULL, formId INT NOT NULL, content LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql("CREATE TABLE EventRegistration (id INT AUTO_INCREMENT NOT NULL, eventId INT NOT NULL, userId INT NOT NULL, status ENUM('pending', 'refused', 'accept') NOT NULL, creationDate DATETIME NOT NULL, modificationDate DATETIME NOT NULL, INDEX IDX_A10E2FDB2B2EBB6C (eventId), PRIMARY KEY(id))");
        $this->addSql('CREATE TABLE EventRegistrationForm (id INT AUTO_INCREMENT NOT NULL, registrationId INT NOT NULL, formId INT NOT NULL, userInput LONGTEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE Event ADD CONSTRAINT fk_Event_cityId FOREIGN KEY (cityId) REFERENCES RoomCity (id) ON  DELETE CASCADE');
        $this->addSql('ALTER TABLE Event ADD CONSTRAINT fk_Event_buildingId FOREIGN KEY (buildingId) REFERENCES RoomBuilding (id) ON  DELETE CASCADE');
        $this->addSql('ALTER TABLE EventAttachment ADD CONSTRAINT fk_EventAttachment_eventId FOREIGN KEY (eventId) REFERENCES Event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE EventDate ADD CONSTRAINT fk_EventDate_eventId FOREIGN KEY (eventId) REFERENCES Event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE EventTime ADD CONSTRAINT fk_EventTime_dateId FOREIGN KEY (dateId) REFERENCES EventDate (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE EventForm ADD CONSTRAINT fk_EventForm_eventId FOREIGN KEY (eventId) REFERENCES Event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE EventFormOption ADD CONSTRAINT fk_EventFormOption_formId FOREIGN KEY (formId) REFERENCES EventForm (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE EventRegistration ADD CONSTRAINT fk_EventRegistration_eventId FOREIGN KEY (eventId) REFERENCES Event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE EventRegistration ADD CONSTRAINT fk_EventRegistration_userId FOREIGN KEY (userId) REFERENCES User (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE EventRegistrationForm ADD CONSTRAINT fk_EventRegistrationForm_registrationId FOREIGN KEY (registrationId) REFERENCES EventRegistration (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE EventRegistrationForm ADD CONSTRAINT fk_EventRegistrationForm_formId FOREIGN KEY (formId) REFERENCES EventForm (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Event DROP FOREIGN KEY fk_Event_cityId');
        $this->addSql('ALTER TABLE Event DROP FOREIGN KEY fk_Event_buildingId');
        $this->addSql('ALTER TABLE EventAttachment DROP FOREIGN KEY fk_EventAttachment_eventId');
        $this->addSql('ALTER TABLE EventDate DROP FOREIGN KEY fk_EventDate_eventId');
        $this->addSql('ALTER TABLE EventTime DROP FOREIGN KEY fk_EventTime_dateId');
        $this->addSql('ALTER TABLE EventForm DROP FOREIGN KEY fk_EventForm_eventId');
        $this->addSql('ALTER TABLE EventRegistration DROP FOREIGN KEY fk_EventRegistration_eventId');
        $this->addSql('ALTER TABLE EventRegistration DROP FOREIGN KEY fk_EventRegistration_userId');
        $this->addSql('ALTER TABLE EventFormOption DROP FOREIGN KEY fk_EventFormOption_formId');
        $this->addSql('ALTER TABLE EventRegistrationForm DROP FOREIGN KEY fk_EventRegistrationForm_registrationId');
        $this->addSql('DROP TABLE Event');
        $this->addSql('DROP TABLE EventAttachment');
        $this->addSql('DROP TABLE EventDate');
        $this->addSql('DROP TABLE EventTime');
        $this->addSql('DROP TABLE EventForm');
        $this->addSql('DROP TABLE EventFormOption');
        $this->addSql('DROP TABLE EventRegistration');
        $this->addSql('DROP TABLE EventRegistrationForm');
    }
}
