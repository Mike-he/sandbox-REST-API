<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161107080251 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user_token DROP FOREIGN KEY FK_BDF55A63EA1CE9BE');
        $this->addSql('ALTER TABLE user_token ADD CONSTRAINT FK_BDF55A63EA1CE9BE FOREIGN KEY (clientId) REFERENCES user_client (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user_token DROP FOREIGN KEY FK_BDF55A63EA1CE9BE');
        $this->addSql('ALTER TABLE user_token ADD CONSTRAINT FK_BDF55A63EA1CE9BE FOREIGN KEY (clientId) REFERENCES user_client (id)');
    }
}
