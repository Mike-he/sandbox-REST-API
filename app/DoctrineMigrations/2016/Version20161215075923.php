<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161215075923 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE lease_has_invited_persons (lease_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_BFB958D3CA542C (lease_id), INDEX IDX_BFB958A76ED395 (user_id), PRIMARY KEY(lease_id, user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE lease_has_invited_persons ADD CONSTRAINT FK_BFB958D3CA542C FOREIGN KEY (lease_id) REFERENCES leases (id)');
        $this->addSql('ALTER TABLE lease_has_invited_persons ADD CONSTRAINT FK_BFB958A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE leases ADD effective_date DATETIME DEFAULT NULL, ADD confirmation_date DATETIME DEFAULT NULL, ADD expiration_date DATETIME DEFAULT NULL, ADD reconfirmation_date DATETIME DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE lease_has_invited_persons');
        $this->addSql('ALTER TABLE leases DROP effective_date, DROP confirmation_date, DROP expiration_date, DROP reconfirmation_date');
    }
}
