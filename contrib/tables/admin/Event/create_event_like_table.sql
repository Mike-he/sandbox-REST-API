CREATE TABLE `EventLike` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `eventId` int(11) NOT NULL,
  `authorId` int(11) NOT NULL,
  `creationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `eventId_authorId_UNIQUE` (`eventId`,`authorId`),
  KEY `fk_EventLike_eventId_idx` (`eventId`),
  KEY `fk_EventLike_authorId_idx` (`authorId`),
  CONSTRAINT `fk_EventLike_eventId` FOREIGN KEY (`eventId`) REFERENCES `Event` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_EventLike_authorId` FOREIGN KEY (`authorId`) REFERENCES `User` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);