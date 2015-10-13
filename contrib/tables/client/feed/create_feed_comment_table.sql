CREATE TABLE `FeedComment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `feedId` int(11) NOT NULL,
  `authorId` int(11) NOT NULL,
  `payload` mediumtext CHARACTER SET utf8mb4 NOT NULL,
  `creationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_feedComment_feedId_idx` (`feedId`),
  KEY `fk_feedComment_authorId` (`authorId`),
  CONSTRAINT `fk_feedComment_feedId` FOREIGN KEY (`feedId`) REFERENCES `Feed` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_feedComment_authorId` FOREIGN KEY (`authorId`) REFERENCES `User` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);
