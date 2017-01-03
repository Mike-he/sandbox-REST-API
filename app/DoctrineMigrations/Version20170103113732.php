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
        $this->addSql('CREATE TABLE sales_company_has_room_types (sales_company_id INT NOT NULL, room_types_id INT NOT NULL, INDEX IDX_2D98194598A7ADBA (sales_company_id), INDEX IDX_2D9819452E54F393 (room_types_id), PRIMARY KEY(sales_company_id, room_types_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sales_company_has_room_types ADD CONSTRAINT FK_2D98194598A7ADBA FOREIGN KEY (sales_company_id) REFERENCES sales_company (id)');
        $this->addSql('ALTER TABLE sales_company_has_room_types ADD CONSTRAINT FK_2D9819452E54F393 FOREIGN KEY (room_types_id) REFERENCES room_types (id)');
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
        $this->addSql('DROP TABLE sales_company_has_room_types');
    }
}
