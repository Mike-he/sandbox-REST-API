<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version2017121610370002 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE commnue_advertising_screen (id INT AUTO_INCREMENT NOT NULL, cover VARCHAR(255) NOT NULL, source VARCHAR(64) NOT NULL, source_id INTEGER DEFAULT NULL, content LONGTEXT,  visible TINYINT(1) DEFAULT 0,  creation_date DATETIME NOT NULL, modification_date DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE commnue_screen_attachment (id INT AUTO_INCREMENT NOT NULL, screenId INT NOT NULL, content LONGTEXT NOT NULL, attachmentType VARCHAR(255) NOT NULL, filename VARCHAR(255) NOT NULL, preview LONGTEXT DEFAULT NULL, size INT NOT NULL, height INT NOT NULL, width INT NOT NULL, INDEX IDX_814A0755CF4EFB83 (screenId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE commnue_advertising_screen ADD isSaved TINYINT(1) NOT NULL, ADD isDefault TINYINT(1) NOT NULL, DROP cover, CHANGE source_id source_id INT NOT NULL, CHANGE visible visible TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE commnue_screen_attachment ADD CONSTRAINT FK_814A0755CF4EFB83 FOREIGN KEY (screenId) REFERENCES commnue_advertising_screen (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE commnue_advertising_screen CHANGE content url LONGTEXT DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE commnue_advertising_screen');
        $this->addSql('DROP TABLE commnue_screen_attachment');
        $this->addSql('ALTER TABLE commnue_advertising_screen ADD cover VARCHAR(255) NOT NULL, DROP isSaved, DROP isDefault, CHANGE source_id source_id INT DEFAULT NULL, CHANGE visible visible TINYINT(1) DEFAULT \'0\'');
        $this->addSql('ALTER TABLE commnue_screen_attachment DROP FOREIGN KEY FK_814A0755CF4EFB83');
        $this->addSql('ALTER TABLE commnue_advertising_screen CHANGE url content LONGTEXT DEFAULT NULL');
    }
}
