<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161207025330 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE admin_permission_group_map (id INT AUTO_INCREMENT NOT NULL, group_id INT DEFAULT NULL, permission_id INT DEFAULT NULL, creation_date DATETIME NOT NULL, INDEX IDX_CAEFC52EFE54D947 (group_id), INDEX IDX_CAEFC52EFED90CCA (permission_id), UNIQUE INDEX group_permission_UNIQUE (group_id, permission_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE admin_permission_groups (id INT AUTO_INCREMENT NOT NULL, group_key VARCHAR(32) NOT NULL, group_name VARCHAR(64) NOT NULL, platform VARCHAR(32) NOT NULL, creation_date DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE admin_position_group_binding (id INT AUTO_INCREMENT NOT NULL, position_id INT DEFAULT NULL, group_id INT DEFAULT NULL, creation_date DATETIME NOT NULL, INDEX IDX_4ED6BAE7DD842E46 (position_id), INDEX IDX_4ED6BAE7FE54D947 (group_id), UNIQUE INDEX group_position_UNIQUE (group_id, position_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE admin_permission_group_map ADD CONSTRAINT FK_CAEFC52EFE54D947 FOREIGN KEY (group_id) REFERENCES admin_permission_groups (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE admin_permission_group_map ADD CONSTRAINT FK_CAEFC52EFED90CCA FOREIGN KEY (permission_id) REFERENCES admin_permission (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE admin_position_group_binding ADD CONSTRAINT FK_4ED6BAE7DD842E46 FOREIGN KEY (position_id) REFERENCES admin_position (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE admin_position_group_binding ADD CONSTRAINT FK_4ED6BAE7FE54D947 FOREIGN KEY (group_id) REFERENCES admin_permission_groups (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE admin_permission DROP FOREIGN KEY FK_2877342F10EE4CEE');
        $this->addSql('DROP INDEX IDX_2877342F10EE4CEE ON admin_permission');
        $this->addSql('ALTER TABLE admin_permission DROP parentId');
        $this->addSql('ALTER TABLE room CHANGE type type VARCHAR(64) NOT NULL');
        $this->addSql('ALTER TABLE room_attachment CHANGE roomType roomType VARCHAR(64) NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE admin_permission_group_map DROP FOREIGN KEY FK_CAEFC52EFE54D947');
        $this->addSql('ALTER TABLE admin_position_group_binding DROP FOREIGN KEY FK_4ED6BAE7FE54D947');
        $this->addSql('DROP TABLE admin_permission_group_map');
        $this->addSql('DROP TABLE admin_permission_groups');
        $this->addSql('DROP TABLE admin_position_group_binding');
        $this->addSql('ALTER TABLE admin_permission ADD parentId INT DEFAULT NULL');
        $this->addSql('ALTER TABLE admin_permission ADD CONSTRAINT FK_2877342F10EE4CEE FOREIGN KEY (parentId) REFERENCES admin_permission (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_2877342F10EE4CEE ON admin_permission (parentId)');
        $this->addSql('ALTER TABLE room CHANGE type type VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE room_attachment CHANGE roomType roomType VARCHAR(255) NOT NULL');
    }
}
