<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151106022141 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE BannerAttachment DROP FOREIGN KEY FK_CEC448B7FE3872FA');
        $this->addSql('ALTER TABLE NewsAttachment DROP FOREIGN KEY fk_NewsAttachments_newsId');
        $this->addSql('DROP TABLE Banner');
        $this->addSql('DROP TABLE BannerAttachment');
        $this->addSql('DROP TABLE News');
        $this->addSql('DROP TABLE NewsAttachment');
        $this->addSql('ALTER TABLE AppInfo CHANGE platform platform VARCHAR(16) NOT NULL');
        $this->addSql('ALTER TABLE EventAttachment CHANGE preview preview LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE FeedComment DROP replyToUserId');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE Banner (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, source VARCHAR(64) NOT NULL, sourceId INT DEFAULT NULL, sortTime VARCHAR(15) NOT NULL, creationDate DATETIME NOT NULL, modificationDate DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE BannerAttachment (id INT AUTO_INCREMENT NOT NULL, bannerId INT NOT NULL, content LONGTEXT NOT NULL, attachmentType VARCHAR(64) NOT NULL, filename VARCHAR(255) NOT NULL, preview LONGTEXT DEFAULT NULL, size INT NOT NULL, INDEX IDX_CEC448B7FE3872FA (bannerId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE News (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(1024) NOT NULL, description LONGTEXT NOT NULL, visible TINYINT(1) NOT NULL, creationDate DATETIME NOT NULL, modificationDate DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE NewsAttachment (id INT AUTO_INCREMENT NOT NULL, newsId INT NOT NULL, content LONGTEXT NOT NULL, attachmentType VARCHAR(255) NOT NULL, filename VARCHAR(255) NOT NULL, preview LONGTEXT NOT NULL, size INT NOT NULL, INDEX fk_NewsAttachments_newsId_idx (newsId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE BannerAttachment ADD CONSTRAINT FK_CEC448B7FE3872FA FOREIGN KEY (bannerId) REFERENCES Banner (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE NewsAttachment ADD CONSTRAINT fk_NewsAttachments_newsId FOREIGN KEY (newsId) REFERENCES News (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE AppInfo CHANGE platform platform VARCHAR(16) NOT NULL');
        $this->addSql('ALTER TABLE EventAttachment CHANGE preview preview LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE FeedComment ADD replyToUserId INT DEFAULT NULL');
    }
}
