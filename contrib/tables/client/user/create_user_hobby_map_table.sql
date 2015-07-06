CREATE TABLE `UserHobbyMap` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `hobbyId` int(11) NOT NULL,
  `creationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId_hobbyId_UNIQUE` (`userId`,`hobbyId`),
  KEY `fk_UserHobbiesMap_userId_idx` (`userId`),
  KEY `fk_UserHobbiesMap_hobbyId_idx` (`hobbyId`),
  CONSTRAINT `fk_UserHobbyMap_userId` FOREIGN KEY (`userId`) REFERENCES `User` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_UserHobbyMap_hobbyId` FOREIGN KEY (`hobbyId`) REFERENCES `UserHobby` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);
