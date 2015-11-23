CREATE TABLE `UserData` (
  `userId` int(11) NOT NULL,
  `cardNo` varchar(32) DEFAULT NULL,
  `credentialNo` varchar(64) NOT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  PRIMARY KEY (`userId`),
  UNIQUE KEY `cardNo_UNIQUE` (`cardNo`),
  UNIQUE KEY `credentialNo_UNIQUE` (`credentialNo`)
);
