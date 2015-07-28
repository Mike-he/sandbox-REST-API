CREATE TABLE `DoorAccess` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `buildingId` int(11) NOT NULL,
  `roomId` int(11) NOT NULL,
  `doorId` varchar(64) NOT NULL,
  `timeId` int(11) NOT NULL,
  `orderId` int(11) NOT NULL,
  `startDate` datetime NOT NULL,
  `endDate` datetime NOT NULL,
  `creationDate` datetime NOT NULL,
  PRIMARY KEY (`id`)
);