CREATE TABLE `EventOrder` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `eventId` int(11) NOT NULL,
  `orderNumber` varchar(255) NOT NULL,
  `payChannel` varchar(255) DEFAULT NULL,
  `userId` int(11) NOT NULL,
  `price` double NOT NULL,
  `status` varchar(64) NOT NULL,
  `paymentDate` datetime DEFAULT NULL,
  `cancelledDate` datetime DEFAULT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_EventOrder_eventId_idx` (`eventId`),
  CONSTRAINT `fk_EventOrder_eventId` FOREIGN KEY (`eventId`) REFERENCES `Event` (`id`)
);