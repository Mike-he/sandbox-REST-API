CREATE TABLE `UserToken` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL,
  `clientId` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `creationDate` char(15) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_UNIQUE` (`token`),
  UNIQUE KEY `username_clientId_UNIQUE` (`username`,`clientId`),
  KEY `fk_UserToken_username_idx` (`username`),
  KEY `fk_UserToken_clientId_idx` (`clientId`),
  CONSTRAINT `fk_UserToken_username` FOREIGN KEY (`username`) REFERENCES `User` (`username`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_UserToken_clientId` FOREIGN KEY (`clientId`) REFERENCES `UserClient` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);
