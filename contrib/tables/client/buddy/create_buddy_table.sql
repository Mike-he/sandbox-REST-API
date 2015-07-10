CREATE TABLE `Buddy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `buddyId` int(11) NOT NULL,
  `creationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_Buddy_userId_idx` (`userId`),
  KEY `fk_Buddy_buddyId_idx` (`buddyId`),
  CONSTRAINT `fk_Buddy_userId` FOREIGN KEY (`userId`) REFERENCES `User` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_Buddy_buddyId` FOREIGN KEY (`buddyId`) REFERENCES `User` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);
