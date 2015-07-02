CREATE TABLE `Product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roomId` int(11) NOT NULL,
  `description` text,
  `visibleUserId` int(11),
  `basePrice` numeric(15,2),
  `unitPrice` enum('hour', 'day', 'month'),
  `private` boolean,
  `renewable` boolean,
  `startDate` date NOT NULL,
  `endDate` date NOT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_Product_roomId_idx` (`roomId`),
  CONSTRAINT `fk_Product_roomId` FOREIGN KEY (`roomId`) REFERENCES `Room` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);