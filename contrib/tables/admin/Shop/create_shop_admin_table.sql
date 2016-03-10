CREATE TABLE `ShopAdmin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL,
  `password` varchar(256) NOT NULL,
  `name` varchar(128) DEFAULT NULL,
  `typeId` int(11) NOT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  `companyId` int(11) NOT NULL,
  `defaultPasswordChanged` tinyint(1) NOT NULL,
  `banned` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username_UNIQUE` (`username`),
  KEY `IDX_57E936B19BF49490` (`typeId`),
  KEY `IDX_57E936B12480E723` (`companyId`),
  CONSTRAINT `FK_57E936B12480E723` FOREIGN KEY (`companyId`) REFERENCES `SalesCompany` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_57E936B19BF49490` FOREIGN KEY (`typeId`) REFERENCES `ShopAdminType` (`id`)
);