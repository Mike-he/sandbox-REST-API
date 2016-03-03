CREATE TABLE `WeChat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) DEFAULT NULL,
  `userClientId` int(11) DEFAULT NULL,
  `openId` varchar(128) NOT NULL,
  `accessToken` varchar(256) DEFAULT NULL,
  `refreshToken` varchar(256) DEFAULT NULL,
  `expiresIn` varchar(16) DEFAULT NULL,
  `scope` varchar(512) DEFAULT NULL,
  `unionId` varchar(256) DEFAULT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `openId_UNIQUE` (`openId`),
  UNIQUE KEY `UNIQ_userId` (`userId`),
  UNIQUE KEY `UNIQ_userClientId` (`userClientId`),
  CONSTRAINT `FK_WeChat_userClientId` FOREIGN KEY (`userClientId`) REFERENCES `UserClient` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_WeChat_userId` FOREIGN KEY (`userId`) REFERENCES `User` (`id`) ON DELETE CASCADE
);
