CREATE TABLE `AdminToken` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL,
  `clientId` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `creationDate` char(15) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_UNIQUE` (`token`),
  UNIQUE KEY `username_clientId_UNIQUE` (`username`,`clientId`),
  KEY `fk_AdminToken_username_idx` (`username`),
  KEY `fk_AdminToken_clientId_idx` (`clientId`),
  CONSTRAINT `fk_AdminToken_username` FOREIGN KEY (`username`) REFERENCES `Admin` (`username`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_AdminToken_clientId` FOREIGN KEY (`clientId`) REFERENCES `AdminClient` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);
