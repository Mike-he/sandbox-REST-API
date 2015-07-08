CREATE TABLE `UserExperience` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `startDate` varchar(16) DEFAULT NULL,
  `endDate` varchar(16) DEFAULT NULL,
  `detail` text NOT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_UserExperience_userId_idx` (`userId`),
  CONSTRAINT `fk_UserExperience_userId` FOREIGN KEY (`userId`) REFERENCES `User` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);
