<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170825093115 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sales_company_service_infos CHANGE service_fee service_fee DOUBLE PRECISION DEFAULT NULL, CHANGE drawer drawer VARCHAR(30) DEFAULT NULL');
        $this->addSql('ALTER TABLE user_bean_flows DROP total');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sales_company_service_infos CHANGE service_fee service_fee DOUBLE PRECISION NOT NULL, CHANGE drawer drawer VARCHAR(30) NOT NULL');
        $this->addSql('ALTER TABLE user_bean_flows ADD total DOUBLE PRECISION NOT NULL');
    }
}
