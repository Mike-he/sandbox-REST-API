CREATE TABLE `Advertising` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` longtext COLLATE utf8_unicode_ci NOT NULL,
  `source` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `sourceId` int(11) DEFAULT NULL,
  `visible` tinyint(1) NOT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  `isSaved` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
)
