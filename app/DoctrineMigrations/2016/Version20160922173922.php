<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160922173922 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE admin_position ADD CONSTRAINT FK_D28CE3F3179BBB64 FOREIGN KEY (parentPositionId) REFERENCES admin_position (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_D28CE3F3179BBB64 ON admin_position (parentPositionId)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE admin_position DROP FOREIGN KEY FK_D28CE3F3179BBB64');
        $this->addSql('DROP INDEX IDX_D28CE3F3179BBB64 ON admin_position');
    }
}
