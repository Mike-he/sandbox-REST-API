CREATE TABLE `FeedLike` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fid` int(11) NOT NULL,
  `authorID` varchar(64) NOT NULL,
  `creationDate` char(15) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `feed_like_fid_authorid_UNIQUE` (`fid`,`authorID`),
  KEY `feed_like_fk_fid_idx` (`fid`),
  CONSTRAINT `fk_feedlike_fid` FOREIGN KEY (`fid`) REFERENCES `Feed` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);
