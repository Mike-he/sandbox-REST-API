CREATE TABLE `FeedLike` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `feedId` int(11) NOT NULL,
  `authorId` varchar(64) NOT NULL,
  `creationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `feed_like_feedId_authorId_UNIQUE` (`feedId`,`authorId`),
  KEY `feed_like_fk_fid_idx` (`feedId`),
  CONSTRAINT `fk_feedLike_fid` FOREIGN KEY (`feedId`) REFERENCES `Feed` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);
