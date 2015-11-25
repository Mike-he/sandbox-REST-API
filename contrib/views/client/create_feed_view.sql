CREATE VIEW FeedView AS
SELECT DISTINCT f.*,
       (SELECT COUNT(fc.id) FROM FeedComment fc LEFT JOIN User u1 ON u1.id = fc.authorId WHERE fc.feedId = f.id AND u1.banned = FALSE ) AS comments_count,
       (SELECT COUNT(fl.id) FROM FeedLike fl LEFT JOIN User u2 ON u2.id = fl.authorId WHERE fl.feedId = f.id AND u2.banned = FALSE ) AS likes_count
FROM Feed AS f;
