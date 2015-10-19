CREATE TABLE `FeedLike` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `feedId` int(11) NOT NULL,
  `authorId` int(11) NOT NULL,
  `creationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `feedId_authorId_UNIQUE` (`feedId`,`authorId`),
  KEY `fk_FeedLike_feedId_idx` (`feedId`),
  KEY `fk_FeedLike_authorId_idx` (`authorId`),
  CONSTRAINT `fk_FeedLike_feedId` FOREIGN KEY (`feedId`) REFERENCES `Feed` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_FeedLike_authorId` FOREIGN KEY (`authorId`) REFERENCES `User` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);
