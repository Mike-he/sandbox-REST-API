CREATE TABLE `WeChat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) DEFAULT NULL,
  `userClientId` int(11) DEFAULT NULL,
  `openId` varchar(256) NOT NULL,
  `accessToken` varchar(256) DEFAULT NULL,
  `refreshToken` varchar(256) DEFAULT NULL,
  `expiresIn` varchar(16) DEFAULT NULL,
  `scope` varchar(512) DEFAULT NULL,
  `unionId` varchar(512) DEFAULT NULL,
  `authCode` varchar(256) DEFAULT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `openId_UNIQUE` (`openId`),
  UNIQUE KEY `userId_UNIQUE` (`userId`),
  UNIQUE KEY `authCode_UNIQUE` (`authCode`),
  KEY `FK_WeChat_userClientId_idx` (`userClientId`),
  CONSTRAINT `FK_WeChat_userId` FOREIGN KEY (`userId`) REFERENCES `User` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_WeChat_userClientId` FOREIGN KEY (`userClientId`) REFERENCES `UserClient` (`id`) ON DELETE NO ACTION
);
