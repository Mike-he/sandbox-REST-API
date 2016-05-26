CREATE TABLE `BulletinPost` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `typeId` int(11) NOT NULL,
  `title` varchar(128) NOT NULL,
  `description` varchar(1024) NOT NULL,
  `content` longtext NOT NULL,
  `deleted` tinyint(1) DEFAULT FALSE NOT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_BulletinPost_typeId_idx` (`typeId`),
  CONSTRAINT `fk_BulletinPost_typeId` FOREIGN KEY (`typeId`) REFERENCES `BulletinType` (`id`) ON DELETE CASCADE
);