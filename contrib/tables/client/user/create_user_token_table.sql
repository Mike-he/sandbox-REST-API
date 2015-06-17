CREATE TABLE `UserToken` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `clientId` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `creationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_UNIQUE` (`token`),
  UNIQUE KEY `userId_clientId_UNIQUE` (`userId`,`clientId`),
  KEY `fk_UserToken_userId_idx` (`userId`),
  KEY `fk_UserToken_clientId_idx` (`clientId`),
  CONSTRAINT `fk_UserToken_userId` FOREIGN KEY (`userId`) REFERENCES `User` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_UserToken_clientId` FOREIGN KEY (`clientId`) REFERENCES `UserClient` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);
