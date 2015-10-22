CREATE TABLE `EventForm` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `eventId` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `type` enum('text','email','phone','radio','checkbox') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_EventForm_eventId_idx` (`eventId`),
  CONSTRAINT `fk_EventForm_eventId` FOREIGN KEY (`eventId`) REFERENCES `Event` (`id`) ON DELETE CASCADE
);