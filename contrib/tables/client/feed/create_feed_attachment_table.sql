CREATE TABLE `FeedAttachment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `feedId` int(11) NOT NULL,
  `content` text NOT NULL,
  `attachmentType` varchar(64) NOT NULL,
  `filename` varchar(64) NOT NULL,
  `preview` text,
  `size` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_feedAttachment_feedId_idx` (`feedId`),
  CONSTRAINT `fk_feedAttachment_feedId` FOREIGN KEY (`feedId`) REFERENCES `Feed` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);
