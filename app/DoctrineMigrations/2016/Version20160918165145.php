<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160918165145 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE admin_position CHANGE parentPositionId parentPositionId INT DEFAULT NULL, CHANGE salesCompanyId salesCompanyId INT DEFAULT NULL');
        $this->addSql('ALTER TABLE admin_position ADD CONSTRAINT FK_D28CE3F3C50DB8C4 FOREIGN KEY (salesCompanyId) REFERENCES sales_company (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE admin_position ADD CONSTRAINT FK_D28CE3F3AC3A2DE FOREIGN KEY (iconId) REFERENCES admin_position_icons (id)');
        $this->addSql('CREATE INDEX IDX_D28CE3F3C50DB8C4 ON admin_position (salesCompanyId)');
        $this->addSql('CREATE INDEX IDX_D28CE3F3AC3A2DE ON admin_position (iconId)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE admin_position DROP FOREIGN KEY FK_D28CE3F3C50DB8C4');
        $this->addSql('ALTER TABLE admin_position DROP FOREIGN KEY FK_D28CE3F3AC3A2DE');
        $this->addSql('DROP INDEX IDX_D28CE3F3C50DB8C4 ON admin_position');
        $this->addSql('DROP INDEX IDX_D28CE3F3AC3A2DE ON admin_position');
        $this->addSql('ALTER TABLE admin_position CHANGE parentPositionId parentPositionId INT NOT NULL, CHANGE salesCompanyId salesCompanyId INT NOT NULL');
    }
}
