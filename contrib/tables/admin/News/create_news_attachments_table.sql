CREATE TABLE `NewsAttachments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `newsId` int(11) NOT NULL,
  `content` longtext NOT NULL,
  `attachmentType` varchar(255) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `preview` longtext NOT NULL,
  `size` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_NewsAttachments_newsId_idx` (`newsId`),
  CONSTRAINT `fk_NewsAttachments_newsId` FOREIGN KEY (`newsId`) REFERENCES `News` (`id`) ON DELETE CASCADE
);