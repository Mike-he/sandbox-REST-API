<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171202035127 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE feed_likes DROP FOREIGN KEY FK_BC451DC116FF8019');
        $this->addSql('ALTER TABLE feed_likes DROP FOREIGN KEY FK_BC451DC1A196F9FD');
        $this->addSql('DROP INDEX IDX_BC451DC116FF8019 ON feed_likes');
        $this->addSql('DROP INDEX IDX_BC451DC1A196F9FD ON feed_likes');
        $this->addSql('ALTER TABLE feed_likes ADD feed_id INT NOT NULL, ADD author_id INT NOT NULL, DROP feedId, DROP authorId');
        $this->addSql('ALTER TABLE commnue_material CHANGE modification_date modification_date DATETIME DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE commnue_material CHANGE modification_date modification_date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE feed_likes ADD feedId INT NOT NULL, ADD authorId INT NOT NULL, DROP feed_id, DROP author_id');
        $this->addSql('ALTER TABLE feed_likes ADD CONSTRAINT FK_BC451DC116FF8019 FOREIGN KEY (feedId) REFERENCES feed (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE feed_likes ADD CONSTRAINT FK_BC451DC1A196F9FD FOREIGN KEY (authorId) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_BC451DC116FF8019 ON feed_likes (feedId)');
        $this->addSql('CREATE INDEX IDX_BC451DC1A196F9FD ON feed_likes (authorId)');
    }
}
