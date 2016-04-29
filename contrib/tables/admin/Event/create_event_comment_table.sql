CREATE TABLE `EventComment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `eventId` int(11) NOT NULL,
  `authorId` int(11) NOT NULL,
  `payload` longtext NOT NULL,
  `replyToUserId` int(11) DEFAULT NULL,
  `creationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_EventComment_eventId_idx` (`eventId`),
  KEY `fk_EventComment_authorId_idx` (`authorId`),
  CONSTRAINT `fk_EventComment_eventId` FOREIGN KEY (`eventId`) REFERENCES `Event` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_EventComment_authorId` FOREIGN KEY (`authorId`) REFERENCES `User` (`id`) ON DELETE CASCADE
);