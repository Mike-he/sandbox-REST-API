<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151022152224 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE EventAttachment DROP FOREIGN KEY fk_EventAttachment_eventId');
        $this->addSql('ALTER TABLE EventForm DROP FOREIGN KEY fk_EventForm_eventId');
        $this->addSql('ALTER TABLE EventRegistration DROP FOREIGN KEY fk_EventRegistration_eventId');
        $this->addSql('ALTER TABLE EventFormOption DROP FOREIGN KEY fk_EventFormOption_formId');
        $this->addSql('ALTER TABLE EventRegistrationForm DROP FOREIGN KEY fk_EventRegistrationForm_formId');
        $this->addSql('ALTER TABLE EventRegistrationForm DROP FOREIGN KEY fk_EventRegistrationForm_registrationId');
        $this->addSql('CREATE TABLE Food (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, cityId INT NOT NULL, buildingId INT NOT NULL, category VARCHAR(255) NOT NULL, price NUMERIC(10, 2) DEFAULT NULL, inventory INT DEFAULT NULL, creationDate DATETIME NOT NULL, modificationDate DATETIME NOT NULL, INDEX IDX_740A86C97F99FC72 (cityId), INDEX IDX_740A86C9F55CF348 (buildingId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE FoodAttachment (id INT AUTO_INCREMENT NOT NULL, foodId INT NOT NULL, content LONGTEXT NOT NULL, attachmentType VARCHAR(64) NOT NULL, filename VARCHAR(255) NOT NULL, preview LONGTEXT NOT NULL, size INT NOT NULL, INDEX IDX_2E006E3533F278DC (foodId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE FoodForm (id INT AUTO_INCREMENT NOT NULL, foodId INT NOT NULL, title VARCHAR(255) NOT NULL, type VARCHAR(64) NOT NULL, required TINYINT(1) NOT NULL, creationDate DATETIME NOT NULL, modificationDate DATETIME NOT NULL, INDEX IDX_B1C0C96033F278DC (foodId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE FoodFormOption (id INT AUTO_INCREMENT NOT NULL, formId INT NOT NULL, name VARCHAR(255) NOT NULL, price NUMERIC(10, 0) NOT NULL, creationDate DATETIME NOT NULL, modificationDate DATETIME NOT NULL, INDEX IDX_30AFA579E50CC11 (formId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE Food ADD CONSTRAINT FK_740A86C97F99FC72 FOREIGN KEY (cityId) REFERENCES RoomCity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE Food ADD CONSTRAINT FK_740A86C9F55CF348 FOREIGN KEY (buildingId) REFERENCES RoomBuilding (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE FoodAttachment ADD CONSTRAINT FK_2E006E3533F278DC FOREIGN KEY (foodId) REFERENCES Food (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE FoodForm ADD CONSTRAINT FK_B1C0C96033F278DC FOREIGN KEY (foodId) REFERENCES Food (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE FoodFormOption ADD CONSTRAINT FK_30AFA579E50CC11 FOREIGN KEY (formId) REFERENCES FoodForm (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE Event');
        $this->addSql('DROP TABLE EventAttachment');
        $this->addSql('DROP TABLE EventForm');
        $this->addSql('DROP TABLE EventFormOption');
        $this->addSql('DROP TABLE EventRegistration');
        $this->addSql('DROP TABLE EventRegistrationForm');
        $this->addSql('ALTER TABLE Product ADD recommend TINYINT(1) NOT NULL, ADD sortTime VARCHAR(15) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE FoodAttachment DROP FOREIGN KEY FK_2E006E3533F278DC');
        $this->addSql('ALTER TABLE FoodForm DROP FOREIGN KEY FK_B1C0C96033F278DC');
        $this->addSql('ALTER TABLE FoodFormOption DROP FOREIGN KEY FK_30AFA579E50CC11');
        $this->addSql('CREATE TABLE Event (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, cityId INT NOT NULL, buildingId INT NOT NULL, roomId INT DEFAULT NULL, limitNumber INT NOT NULL, registrationStartDate DATETIME NOT NULL, registrationEndDate DATETIME NOT NULL, registrationMethod VARCHAR(255) NOT NULL, verify TINYINT(1) NOT NULL, visible TINYINT(1) NOT NULL, eventEndDate DATETIME NOT NULL, creationDate DATETIME NOT NULL, modificationDate DATETIME NOT NULL, INDEX fk_Event_cityId (cityId), INDEX fk_Event_buildingId (buildingId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE EventAttachment (id INT AUTO_INCREMENT NOT NULL, eventId INT NOT NULL, content LONGTEXT NOT NULL, attachmentType VARCHAR(64) NOT NULL, filename VARCHAR(255) NOT NULL, preview LONGTEXT NOT NULL, size INT NOT NULL, INDEX IDX_75CE7BD52B2EBB6C (eventId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE EventForm (id INT AUTO_INCREMENT NOT NULL, eventId INT NOT NULL, title VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, INDEX fk_EventForm_eventId (eventId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE EventFormOption (id INT AUTO_INCREMENT NOT NULL, formId INT NOT NULL, content LONGTEXT NOT NULL, INDEX fk_EventFormOption_formId (formId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE EventRegistration (id INT AUTO_INCREMENT NOT NULL, eventId INT NOT NULL, userId INT NOT NULL, status VARCHAR(255) NOT NULL, creationDate DATETIME NOT NULL, modificationDate DATETIME NOT NULL, INDEX IDX_A10E2FDB2B2EBB6C (eventId), INDEX fk_EventRegistration_userId (userId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE EventRegistrationForm (id INT AUTO_INCREMENT NOT NULL, registrationId INT NOT NULL, formId INT NOT NULL, userInput LONGTEXT NOT NULL, INDEX fk_EventRegistrationForm_registrationId (registrationId), INDEX fk_EventRegistrationForm_formId (formId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE Event ADD CONSTRAINT fk_Event_buildingId FOREIGN KEY (buildingId) REFERENCES RoomBuilding (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE Event ADD CONSTRAINT fk_Event_cityId FOREIGN KEY (cityId) REFERENCES RoomCity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE EventAttachment ADD CONSTRAINT fk_EventAttachment_eventId FOREIGN KEY (eventId) REFERENCES Event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE EventForm ADD CONSTRAINT fk_EventForm_eventId FOREIGN KEY (eventId) REFERENCES Event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE EventFormOption ADD CONSTRAINT fk_EventFormOption_formId FOREIGN KEY (formId) REFERENCES EventForm (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE EventRegistration ADD CONSTRAINT fk_EventRegistration_eventId FOREIGN KEY (eventId) REFERENCES Event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE EventRegistration ADD CONSTRAINT fk_EventRegistration_userId FOREIGN KEY (userId) REFERENCES User (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE EventRegistrationForm ADD CONSTRAINT fk_EventRegistrationForm_formId FOREIGN KEY (formId) REFERENCES EventForm (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE EventRegistrationForm ADD CONSTRAINT fk_EventRegistrationForm_registrationId FOREIGN KEY (registrationId) REFERENCES EventRegistration (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE Food');
        $this->addSql('DROP TABLE FoodAttachment');
        $this->addSql('DROP TABLE FoodForm');
        $this->addSql('DROP TABLE FoodFormOption');
        $this->addSql('ALTER TABLE Product DROP recommend, DROP sortTime');
    }
}
