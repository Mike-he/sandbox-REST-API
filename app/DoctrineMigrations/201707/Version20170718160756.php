<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170718160756 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE generic_list (id INT AUTO_INCREMENT NOT NULL, platform VARCHAR(16) NOT NULL, object VARCHAR(16) NOT NULL, `column` VARCHAR(32) NOT NULL, name VARCHAR(32) NOT NULL, `default` TINYINT(1) NOT NULL, required TINYINT(1) NOT NULL, sort TINYINT(1) NOT NULL, direction VARCHAR(16) DEFAULT NULL, creationDate DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE generic_user_list (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, platform VARCHAR(16) NOT NULL, object VARCHAR(16) NOT NULL, `column` VARCHAR(32) NOT NULL, required TINYINT(1) NOT NULL, sort TINYINT(1) NOT NULL, direction VARCHAR(16) DEFAULT NULL, creationDate DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE generic_list');
        $this->addSql('DROP TABLE generic_user_list');
    }
}
