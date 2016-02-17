CREATE TABLE `Shop` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `description` text,
  `buildingId` int(11) NOT NULL,
  `startHour` time NOT NULL,
  `endHour` time NOT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_Shop_buildingId_idx` (`buildingId`),
  CONSTRAINT `fk_Shop_buildingId` FOREIGN KEY (`buildingId`) REFERENCES `RoomBuilding` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);