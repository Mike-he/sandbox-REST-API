<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161021093302 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE evaluation (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, totalStar DOUBLE PRECISION NOT NULL, serviceStar DOUBLE PRECISION NOT NULL, environmentStar DOUBLE PRECISION NOT NULL, comment LONGTEXT NOT NULL, userId INT NOT NULL, buildingId INT NOT NULL, productOrderId INT DEFAULT NULL, creationDate DATETIME NOT NULL, INDEX IDX_1323A57564B64DCC (userId), INDEX IDX_1323A575F55CF348 (buildingId), INDEX IDX_1323A575AED1EB8C (productOrderId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE evaluation_attachment (id INT AUTO_INCREMENT NOT NULL, evaluationId INT NOT NULL, content LONGTEXT NOT NULL, attachmentType VARCHAR(64) NOT NULL, filename VARCHAR(255) NOT NULL, preview LONGTEXT DEFAULT NULL, size INT NOT NULL, INDEX IDX_93589E49DE498A05 (evaluationId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE evaluation ADD CONSTRAINT FK_1323A57564B64DCC FOREIGN KEY (userId) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE evaluation ADD CONSTRAINT FK_1323A575F55CF348 FOREIGN KEY (buildingId) REFERENCES room_building (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE evaluation ADD CONSTRAINT FK_1323A575AED1EB8C FOREIGN KEY (productOrderId) REFERENCES product_order (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE evaluation_attachment ADD CONSTRAINT FK_93589E49DE498A05 FOREIGN KEY (evaluationId) REFERENCES evaluation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE room_building ADD evalutionStar DOUBLE PRECISION DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE evaluation_attachment DROP FOREIGN KEY FK_93589E49DE498A05');
        $this->addSql('DROP TABLE evaluation');
        $this->addSql('DROP TABLE evaluation_attachment');
        $this->addSql('ALTER TABLE room_building DROP evalutionStar');
    }
}
