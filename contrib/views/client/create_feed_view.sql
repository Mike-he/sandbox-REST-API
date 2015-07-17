CREATE VIEW FeedView AS
SELECT
       f.*,
       COUNT(DISTINCT fc.id) AS comments_count,
	     COUNT(DISTINCT fl.id) AS likes_count
FROM Feed f
LEFT JOIN FeedComment fc ON fc.feedId = f.id
LEFT JOIN FeedLike fl ON fc.feedId = f.id
GROUP BY f.id
;