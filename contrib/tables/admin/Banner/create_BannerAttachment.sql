CREATE TABLE `BannerAttachment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bannerId` int(11) NOT NULL,
  `content` longtext NOT NULL,
  `attachmentType` varchar(64) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `preview` longtext,
  `size` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_BannerAttachment_bannerId_idx` (`bannerId`),
  CONSTRAINT `fk_BannerAttachment_bannerId` FOREIGN KEY (`bannerId`) REFERENCES `Banner` (`id`) ON DELETE CASCADE
);