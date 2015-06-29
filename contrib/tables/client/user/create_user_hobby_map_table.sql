CREATE TABLE `UserHobbyMap` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`userId` int(11) NOT NULL,
`hobbyId` int(11) NOT NULL,
PRIMARY KEY (`id`),
KEY `fk_UserHobbiesMap_userId_idx` (`userId`),
KEY `fk_UserHobbiesMap_hobbyId_idx` (`hobbyId`),
CONSTRAINT `fk_UserHobbyMap_hobbyId` FOREIGN KEY (`hobbyId`) REFERENCES `Hobby` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);