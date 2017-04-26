<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920170424083212 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs

        $this->addSql('
            CREATE OR REPLACE VIEW user_view AS
            SELECT
                   u.id,
                   u.phone,
                   u.email,
                   u.banned,
                   u.authorized,
                   u.cardNo,
                   u.credentialNo,
                   u.authorizedPlatform,
                   u.authorizedAdminUsername,
                   up.name,
                   up.gender,
                   u.creationDate as userRegistrationDate,
                   u.bean as bean
            FROM user u
            LEFT JOIN user_profiles up ON u.id = up.userId
            ;
        ');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
