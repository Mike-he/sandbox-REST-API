<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151015094003_bug_13300 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP VIEW FeedView');
        $this->addSql('CREATE VIEW FeedView AS
                        SELECT DISTINCT f.*,
                               (SELECT COUNT(fc.id) FROM FeedComment fc LEFT JOIN User u1 ON u1.id = fc.authorId WHERE fc.feedId = f.id AND u1.banned = FALSE AND u1.authorized = TRUE ) AS comments_count,
                               (SELECT COUNT(fl.id) FROM FeedLike fl LEFT JOIN User u2 ON u2.id = fl.authorId WHERE fl.feedId = f.id AND u2.banned = FALSE AND u2.authorized = TRUE ) AS likes_count
                        FROM Feed f
                        LEFT JOIN User u ON u.id = f.ownerId
                        WHERE u.banned = FALSE
                        AND u.authorized = TRUE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE VIEW FeedView AS
                        SELECT DISTINCT f.*,
                               (SELECT COUNT(fc.id) FROM FeedComment fc LEFT JOIN User u1 ON u1.id = fc.authorId WHERE fc.feedId = f.id AND u1.banned = FALSE AND u1.authorized = TRUE ) AS comments_count,
                               (SELECT COUNT(fl.id) FROM FeedLike fl LEFT JOIN User u2 ON u2.id = fl.authorId WHERE fl.feedId = f.id AND u2.banned = FALSE AND u2.authorized = TRUE ) AS likes_count
                        FROM Feed f
                        LEFT JOIN User u ON u.id = f.ownerId
                        WHERE u.banned = FALSE
                        AND u.authorized = TRUE');
    }
}
