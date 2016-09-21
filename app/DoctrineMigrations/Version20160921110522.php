<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160921110522 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE admin_position ADD CONSTRAINT FK_D28CE3F3C50DB8C4 FOREIGN KEY (salesCompanyId) REFERENCES sales_company (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_D28CE3F3C50DB8C4 ON admin_position (salesCompanyId)');
        $this->addSql('ALTER TABLE admin_position_user_binding ADD CONSTRAINT FK_EF4057EFF55CF348 FOREIGN KEY (buildingId) REFERENCES room_building (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE admin_position_user_binding ADD CONSTRAINT FK_EF4057EFC9E63C48 FOREIGN KEY (shopId) REFERENCES shop (id) ON DELETE SET NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE admin_position DROP FOREIGN KEY FK_D28CE3F3C50DB8C4');
        $this->addSql('DROP INDEX IDX_D28CE3F3C50DB8C4 ON admin_position');
        $this->addSql('ALTER TABLE user_check_codes DROP adminId');
    }
}
