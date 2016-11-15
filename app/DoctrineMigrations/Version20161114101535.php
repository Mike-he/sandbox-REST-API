<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161114101535 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE admin_platform DROP FOREIGN KEY FK_ADF2D7CDEA1CE9BE');
        $this->addSql('ALTER TABLE admin_platform ADD CONSTRAINT FK_ADF2D7CDEA1CE9BE FOREIGN KEY (clientId) REFERENCES user_client (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE admin_platform DROP FOREIGN KEY FK_ADF2D7CDEA1CE9BE');
        $this->addSql('ALTER TABLE admin_platform ADD CONSTRAINT FK_ADF2D7CDEA1CE9BE FOREIGN KEY (clientId) REFERENCES user_client (id)');
    }
}
