CREATE TABLE `RoomDoors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roomId` int(11) NOT NULL,
  `doorControlId` varchar(255) NOT NULL,
  `name` varchar(64) NOT NULL,
  `creationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roomId_doorControlId` (`roomId`,`doorControlId`),
  KEY `fk_RoomDoors_roomId_idx` (`roomId`),
  CONSTRAINT `fk_RoomDoors_roomId` FOREIGN KEY (`roomId`) REFERENCES `Room` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);