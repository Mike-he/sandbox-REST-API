CREATE TABLE `UserToken` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `clientId` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `creationDate` char(15) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_UNIQUE` (`token`),
  UNIQUE KEY `userClient_UNIQUE` (`userId`,`clientId`),
  KEY `fk_user_token_userId_idx` (`userId`),
  KEY `fk_user_token_clientId_idx` (`clientId`),
  CONSTRAINT `fk_user_token_clientId` FOREIGN KEY (`clientId`) REFERENCES `jtClient` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_user_token_userId` FOREIGN KEY (`userId`) REFERENCES `jtUser` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
);
