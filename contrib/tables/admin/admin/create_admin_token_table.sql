CREATE TABLE `AdminToken` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `adminId` int(11) NOT NULL,
  `clientId` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `creationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_UNIQUE` (`token`),
  UNIQUE KEY `adminId_clientId_UNIQUE` (`adminId`,`clientId`),
  KEY `fk_AdminToken_adminId_idx` (`adminId`),
  KEY `fk_AdminToken_clientId_idx` (`clientId`),
  CONSTRAINT `fk_AdminToken_adminId` FOREIGN KEY (`adminId`) REFERENCES `Admin` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_AdminToken_clientId` FOREIGN KEY (`clientId`) REFERENCES `AdminClient` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);
