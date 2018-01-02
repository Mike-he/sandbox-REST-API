<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170103113732 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE room_building_type_binding');
        $this->addSql('CREATE TABLE sales_company_service_infos (id INT AUTO_INCREMENT NOT NULL, company_id INT DEFAULT NULL, room_types VARCHAR(30) NOT NULL, service_fee DOUBLE PRECISION NOT NULL, collection_method VARCHAR(30) NOT NULL, drawer VARCHAR(30) NOT NULL, invoicing_subjects VARCHAR(60) DEFAULT NULL, status TINYINT(1) NOT NULL, INDEX IDX_AF25B3D7979B1AD6 (company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sales_company_service_infos ADD CONSTRAINT FK_AF25B3D7979B1AD6 FOREIGN KEY (company_id) REFERENCES sales_company (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE room_building_type_binding (id INT AUTO_INCREMENT NOT NULL, creationDate DATETIME NOT NULL, buildingId INT NOT NULL, typeId INT NOT NULL, UNIQUE INDEX typeId_buildingId (typeId, buildingId), INDEX IDX_BAC0350F55CF348 (buildingId), INDEX IDX_BAC03509BF49490 (typeId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE room_building_type_binding ADD CONSTRAINT FK_BAC03509BF49490 FOREIGN KEY (typeId) REFERENCES room_types (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE room_building_type_binding ADD CONSTRAINT FK_BAC0350F55CF348 FOREIGN KEY (buildingId) REFERENCES room_building (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE sales_company_service_infos');
    }
}
