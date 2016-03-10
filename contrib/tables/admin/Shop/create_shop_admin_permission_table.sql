CREATE TABLE `ShopAdminPermission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(32) NOT NULL,
  `name` varchar(64) NOT NULL,
  `typeId` int(11) NOT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_UNIQUE` (`key`),
  KEY `IDX_E9E621569BF49490` (`typeId`),
  CONSTRAINT `FK_E9E621569BF49490` FOREIGN KEY (`typeId`) REFERENCES `ShopAdminType` (`id`) ON DELETE CASCADE
);