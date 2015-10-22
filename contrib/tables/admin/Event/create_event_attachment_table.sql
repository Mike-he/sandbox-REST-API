CREATE TABLE `EventAttachment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `eventId` int(11) NOT NULL,
  `content` longtext NOT NULL,
  `attachmentType` varchar(64) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `preview` longtext NOT NULL,
  `size` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_EventAttachment_eventId_idx` (`eventId`),
  CONSTRAINT `fk_EventAttachment_eventId` FOREIGN KEY (`eventId`) REFERENCES `Event` (`id`) ON DELETE CASCADE
);