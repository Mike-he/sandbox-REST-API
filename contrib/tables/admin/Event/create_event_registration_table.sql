CREATE TABLE `EventRegistration` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `eventId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `status` enum('pending','refused','accept') NOT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_EventRegistration_eventId_idx` (`eventId`),
  KEY `fk_EventRegistration_userId_idx` (`userId`),
  CONSTRAINT `fk_EventRegistration_userId` FOREIGN KEY (`userId`) REFERENCES `User` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_EventRegistration_eventId` FOREIGN KEY (`eventId`) REFERENCES `Event` (`id`) ON DELETE CASCADE
);