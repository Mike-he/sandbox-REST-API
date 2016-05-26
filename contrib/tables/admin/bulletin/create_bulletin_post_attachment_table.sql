CREATE TABLE `BulletinPostAttachment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `postId` int(11) NOT NULL,
  `content` longtext NOT NULL,
  `attachmentType` varchar(255) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `preview` longtext,
  `size` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_BulletinPostAttachment_postId_idx` (`postId`),
  CONSTRAINT `fk_BulletinPostAttachment_postId` FOREIGN KEY (`postId`) REFERENCES `BulletinPost` (`id`) ON DELETE CASCADE
);