<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151022152807 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE Food (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, cityId INT NOT NULL, buildingId INT NOT NULL, category VARCHAR(255) NOT NULL, price NUMERIC(10, 2) DEFAULT NULL, inventory INT DEFAULT NULL, creationDate DATETIME NOT NULL, modificationDate DATETIME NOT NULL, INDEX IDX_740A86C97F99FC72 (cityId), INDEX IDX_740A86C9F55CF348 (buildingId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE FoodAttachment (id INT AUTO_INCREMENT NOT NULL, foodId INT NOT NULL, content LONGTEXT NOT NULL, attachmentType VARCHAR(64) NOT NULL, filename VARCHAR(255) NOT NULL, preview LONGTEXT NOT NULL, size INT NOT NULL, INDEX IDX_2E006E3533F278DC (foodId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE FoodForm (id INT AUTO_INCREMENT NOT NULL, foodId INT NOT NULL, title VARCHAR(255) NOT NULL, type VARCHAR(64) NOT NULL, required TINYINT(1) NOT NULL, creationDate DATETIME NOT NULL, modificationDate DATETIME NOT NULL, INDEX IDX_B1C0C96033F278DC (foodId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE FoodFormOption (id INT AUTO_INCREMENT NOT NULL, formId INT NOT NULL, name VARCHAR(255) NOT NULL, price NUMERIC(10, 0) NOT NULL, creationDate DATETIME NOT NULL, modificationDate DATETIME NOT NULL, INDEX IDX_30AFA579E50CC11 (formId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE Food ADD CONSTRAINT FK_740A86C97F99FC72 FOREIGN KEY (cityId) REFERENCES RoomCity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE Food ADD CONSTRAINT FK_740A86C9F55CF348 FOREIGN KEY (buildingId) REFERENCES RoomBuilding (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE FoodAttachment ADD CONSTRAINT FK_2E006E3533F278DC FOREIGN KEY (foodId) REFERENCES Food (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE FoodForm ADD CONSTRAINT FK_B1C0C96033F278DC FOREIGN KEY (foodId) REFERENCES Food (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE FoodFormOption ADD CONSTRAINT FK_30AFA579E50CC11 FOREIGN KEY (formId) REFERENCES FoodForm (id) ON DELETE CASCADE');
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
        $this->addSql('DROP TABLE Food');
        $this->addSql('DROP TABLE FoodAttachment');
        $this->addSql('DROP TABLE FoodForm');
        $this->addSql('DROP TABLE FoodFormOption');
        $this->addSql('ALTER TABLE Product DROP recommend, DROP sortTime');
    }
}
