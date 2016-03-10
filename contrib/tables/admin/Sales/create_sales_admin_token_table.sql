CREATE TABLE `SalesAdminToken` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `adminId` int(11) NOT NULL,
  `clientId` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `creationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_UNIQUE` (`token`),
  UNIQUE KEY `adminId_clientId_UNIQUE` (`adminId`,`clientId`),
  KEY `adminId_idx` (`adminId`),
  KEY `clientId_idx` (`clientId`),
  CONSTRAINT `fk_adminId_idx` FOREIGN KEY (`adminId`) REFERENCES `SalesAdmin` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_clientId_idx` FOREIGN KEY (`clientId`) REFERENCES `SalesAdminClient` (`id`) ON DELETE CASCADE
);