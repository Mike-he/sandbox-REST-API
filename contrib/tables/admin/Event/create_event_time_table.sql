CREATE TABLE `EventTime` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dateId` int(11) NOT NULL,
  `startTime` datetime NOT NULL,
  `endTime` datetime NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_EventTime_dateId_idx` (`dateId`),
  CONSTRAINT `fk_EventTime_dateId` FOREIGN KEY (`dateId`) REFERENCES `EventDate` (`id`) ON DELETE CASCADE
);