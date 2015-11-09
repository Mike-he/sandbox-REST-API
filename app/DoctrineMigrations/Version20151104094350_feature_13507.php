<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151104094350_feature_13507 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE News (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(1024) NOT NULL, description VARCHAR(1024) NOT NULL, content LONGTEXT NOT NULL, visible TINYINT(1) NOT NULL, creationDate DATETIME NOT NULL, modificationDate DATETIME NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE NewsAttachment (id INT AUTO_INCREMENT NOT NULL, newsId INT NOT NULL, content LONGTEXT NOT NULL, attachmentType VARCHAR(255) NOT NULL, filename VARCHAR(255) NOT NULL, preview LONGTEXT, size INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE NewsAttachment ADD CONSTRAINT fk_NewsAttachment_newsId FOREIGN KEY (newsId) REFERENCES News(id) ON DELETE CASCADE');
        $this->addSql("INSERT INTO AdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2, 'platform.news', '新闻管理', '2015-08-24 00:00:00', '2015-08-24 00:00:00')");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE News');
        $this->addSql('DROP TABLE NewsAttachment');
        $this->addSql('ALTER TABLE NewsAttachment DROP fk_NewsAttachments_newsId');
    }
}