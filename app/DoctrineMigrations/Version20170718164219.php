<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170718164219 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE generic_user_list ADD list_id INT DEFAULT NULL, DROP platform, DROP `column`, DROP required, DROP sort, DROP direction');
        $this->addSql('ALTER TABLE generic_user_list ADD CONSTRAINT FK_4889246E3DAE168B FOREIGN KEY (list_id) REFERENCES generic_list (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_4889246E3DAE168B ON generic_user_list (list_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE generic_user_list DROP FOREIGN KEY FK_4889246E3DAE168B');
        $this->addSql('DROP INDEX IDX_4889246E3DAE168B ON generic_user_list');
        $this->addSql('ALTER TABLE generic_user_list ADD platform VARCHAR(16) NOT NULL, ADD `column` VARCHAR(32) NOT NULL, ADD required TINYINT(1) NOT NULL, ADD sort TINYINT(1) NOT NULL, ADD direction VARCHAR(16) DEFAULT NULL, DROP list_id');
    }
}
