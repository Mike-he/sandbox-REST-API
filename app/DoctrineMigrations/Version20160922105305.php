<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160922105305 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE admin_permission ADD opLevelSelect VARCHAR(16) NOT NULL');
        $this->addSql('ALTER TABLE admin_exclude_permission ADD platform VARCHAR(16) NOT NULL');
        $this->addSql('ALTER TABLE admin_permission ADD maxOpLevel INT NOT NULL');
        $this->addSql('ALTER TABLE admin_exclude_permission DROP FOREIGN KEY FK_D18B3F8EC50DB8C4');
        $this->addSql('ALTER TABLE admin_exclude_permission CHANGE salesCompanyId salesCompanyId INT DEFAULT NULL');
        $this->addSql('ALTER TABLE admin_exclude_permission ADD CONSTRAINT FK_D18B3F8EC50DB8C4 FOREIGN KEY (salesCompanyId) REFERENCES sales_company (id) ON DELETE SET NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE admin_permission DROP opLevelSelect');
        $this->addSql('ALTER TABLE admin_permission DROP opLevelSelect');
        $this->addSql('ALTER TABLE admin_permission DROP maxOpLevel');
        $this->addSql('ALTER TABLE admin_exclude_permission DROP FOREIGN KEY FK_D18B3F8EC50DB8C4');
        $this->addSql('ALTER TABLE admin_exclude_permission CHANGE salesCompanyId salesCompanyId INT NOT NULL');
        $this->addSql('ALTER TABLE admin_exclude_permission ADD CONSTRAINT FK_D18B3F8EC50DB8C4 FOREIGN KEY (salesCompanyId) REFERENCES sales_company (id) ON DELETE CASCADE');
    }
}
