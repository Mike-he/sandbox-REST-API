CREATE VIEW feed_view AS
SELECT DISTINCT f.*,
       (SELECT COUNT(fc.id) FROM feed_comment fc LEFT JOIN user u1 ON u1.id = fc.authorId WHERE fc.feedId = f.id AND u1.banned = FALSE ) AS comments_count,
       (SELECT COUNT(fl.id) FROM feed_likes fl LEFT JOIN user u2 ON u2.id = fl.authorId WHERE fl.feedId = f.id AND u2.banned = FALSE ) AS likes_count
FROM feed AS f;
