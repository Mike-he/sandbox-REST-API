<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161102161008 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE admin_position DROP FOREIGN KEY FK_D28CE3F3AC3A2DE');
        $this->addSql('ALTER TABLE admin_position ADD CONSTRAINT FK_D28CE3F3AC3A2DE FOREIGN KEY (iconId) REFERENCES admin_position_icons (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE admin_position DROP FOREIGN KEY FK_D28CE3F3AC3A2DE');
        $this->addSql('ALTER TABLE admin_position ADD CONSTRAINT FK_D28CE3F3AC3A2DE FOREIGN KEY (iconId) REFERENCES admin_position_icons (id)');
    }
}
