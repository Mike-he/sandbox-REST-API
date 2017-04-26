<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161018031011 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE admin_platform (id INT AUTO_INCREMENT NOT NULL, userId INT NOT NULL, clientId INT NOT NULL, platform VARCHAR(16) NOT NULL, salesCompanyId INT DEFAULT NULL, creationDate DATETIME NOT NULL, INDEX IDX_ADF2D7CD64B64DCC (userId), INDEX IDX_ADF2D7CDEA1CE9BE (clientId), INDEX IDX_ADF2D7CDC50DB8C4 (salesCompanyId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE admin_platform ADD CONSTRAINT FK_ADF2D7CD64B64DCC FOREIGN KEY (userId) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE admin_platform ADD CONSTRAINT FK_ADF2D7CDEA1CE9BE FOREIGN KEY (clientId) REFERENCES user_client (id)');
        $this->addSql('ALTER TABLE admin_platform ADD CONSTRAINT FK_ADF2D7CDC50DB8C4 FOREIGN KEY (salesCompanyId) REFERENCES sales_company (id) ON DELETE SET NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE admin_platform');
    }
}
