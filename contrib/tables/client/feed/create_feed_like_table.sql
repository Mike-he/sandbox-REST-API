CREATE TABLE `FeedLike` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `feedId` int(11) NOT NULL,
  `authorId` varchar(64) NOT NULL,
  `creationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `feed_like_feedId_authorId_UNIQUE` (`feedId`,`authorId`),
  KEY `feed_like_fk_feedId_idx` (`feedId`),
  KEY `fk_feedlike_authorId_idx` (`authorId`),
  CONSTRAINT `fk_feedLike_feedId` FOREIGN KEY (`feedId`) REFERENCES `Feed` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_feedlike_authorId` FOREIGN KEY (`authorId`) REFERENCES `User` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);
