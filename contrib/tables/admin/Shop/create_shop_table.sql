CREATE TABLE `Shop` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64),
  `description` text,
  `buildingId` int(11) NOT NULL,
  `startHour` time,
  `endHour` time,
  `online` boolean DEFAULT FALSE NOT NULL,
  `active` boolean DEFAULT FALSE NOT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_Shop_buildingId_idx` (`buildingId`),
  CONSTRAINT `fk_Shop_buildingId` FOREIGN KEY (`buildingId`) REFERENCES `RoomBuilding` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);