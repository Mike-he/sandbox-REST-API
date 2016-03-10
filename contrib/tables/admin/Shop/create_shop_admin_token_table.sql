CREATE TABLE `ShopAdminToken` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `adminId` int(11) NOT NULL,
  `clientId` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `creationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_UNIQUE` (`token`),
  UNIQUE KEY `adminId_clientId_UNIQUE` (`adminId`,`clientId`),
  KEY `IDX_35D8285A2D696931` (`adminId`),
  KEY `IDX_35D8285AEA1CE9BE` (`clientId`),
  CONSTRAINT `FK_35D8285A2D696931` FOREIGN KEY (`adminId`) REFERENCES `ShopAdmin` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_35D8285AEA1CE9BE` FOREIGN KEY (`clientId`) REFERENCES `ShopAdminClient` (`id`) ON DELETE CASCADE
);