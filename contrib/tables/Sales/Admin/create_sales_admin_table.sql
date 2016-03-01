CREATE TABLE `SalesAdmin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL,
  `password` varchar(256) NOT NULL,
  `name` varchar(128) DEFAULT NULL,
  `typeId` int(11) NOT NULL,
  `companyId` int(11) NOT NULL,
  `defaultPasswordChanged` tinyint(1) NOT NULL,
  `banned` tinyint(1) NOT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username_UNIQUE` (`username`),
  KEY `typeId_idx` (`typeId`),
  KEY `companyId_idx` (`companyId`),
  CONSTRAINT `fk_companyId_idx` FOREIGN KEY (`companyId`) REFERENCES `SalesCompany` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_typeId_idx` FOREIGN KEY (`typeId`) REFERENCES `SalesAdminType` (`id`)
);