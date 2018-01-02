<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171207083757 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs

        $this->addSql('
            CREATE OR REPLACE VIEW feed_view AS
            SELECT DISTINCT f.*,
                   (SELECT COUNT(fc.id) FROM feed_comment fc LEFT JOIN user u1 ON u1.id = fc.authorId WHERE fc.feedId = f.id AND u1.banned = FALSE ) AS comments_count,
                   (SELECT COUNT(fl.id) FROM feed_likes fl LEFT JOIN user u2 ON u2.id = fl.authorId WHERE fl.feedId = f.id AND u2.banned = FALSE ) AS likes_count
            FROM feed AS f;
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
