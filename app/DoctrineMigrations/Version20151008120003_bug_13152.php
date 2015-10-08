<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151008120003_bug_13152 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP VIEW RoomView');
        $this->addSql('DROP VIEW FeedView');
        $this->addSql('CREATE VIEW FeedView AS
                        SELECT DISTINCT f.*,
                               (SELECT COUNT(fc.id) FROM FeedComment fc LEFT JOIN User u1 ON u1.id = fc.authorId WHERE fc.feedId = f.id AND u1.banned = FALSE) AS comments_count,
                               (SELECT COUNT(fl.id) FROM FeedLike fl LEFT JOIN User u2 ON u2.id = fl.authorId WHERE fl.feedId = f.id AND u2.banned = FALSE) AS likes_count
                        FROM Feed f
                        LEFT JOIN User u ON u.id = f.ownerId
                        WHERE u.banned = FALSE');
        $this->addSql('CREATE VIEW RoomView AS
                        SELECT
                            r.*,
                            o.status,
                            o.startDate as orderStartDate,
                            o.endDate as orderEndDate,
                            up.userId as renterId,
                            up.name as renterName,
                            up.email as renterEmail
                        FROM Room r
                        JOIN RoomFloor rf ON rf.id = r.floorId
                        LEFT JOIN Product p ON r.id = p.roomId
                        LEFT JOIN ProductOrder o ON p.id = o.productId
                        LEFT JOIN UserProfile up ON o.userId = up.userId');
        $this->addSql('ALTER TABLE FeedLike CHANGE authorId authorId INT(11) NOT NULL');
        $this->addSql('ALTER TABLE FeedLike ADD CONSTRAINT fk_FeedLike_authorId FOREIGN KEY (authorId) REFERENCES User (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE FeedComment ADD CONSTRAINT fk_FeedComment_authorId FOREIGN KEY (authorId) REFERENCES User (id) ON DELETE CASCADE');
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
                               (SELECT COUNT(fc.id) FROM FeedComment fc LEFT JOIN User u1 ON u1.id = fc.authorId WHERE fc.feedId = f.id AND u1.banned = FALSE) AS comments_count,
                               (SELECT COUNT(fl.id) FROM FeedLike fl LEFT JOIN User u2 ON u2.id = fl.authorId WHERE fl.feedId = f.id AND u2.banned = FALSE) AS likes_count
                        FROM Feed f
                        LEFT JOIN User u ON u.id = f.ownerId
                        WHERE u.banned = FALSE');
        $this->addSql('CREATE VIEW RoomView AS
                        SELECT
                            r.*,
                            o.status,
                            o.startDate as orderStartDate,
                            o.endDate as orderEndDate,
                            up.userId as renterId,
                            up.name as renterName,
                            up.email as renterEmail
                        FROM Room r
                        JOIN RoomFloor rf ON rf.id = r.floorId
                        LEFT JOIN Product p ON r.id = p.roomId
                        LEFT JOIN ProductOrder o ON p.id = o.productId
                        LEFT JOIN UserProfile up ON o.userId = up.userId');
        $this->addSql('ALTER TABLE FeedLike CHANGE authorId authorId VARCHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE FeedLike DROP FOREIGN KEY fk_FeedLike_authorId');
        $this->addSql('ALTER TABLE FeedComment DROP FOREIGN KEY fk_FeedComment_authorId');
    }
}
