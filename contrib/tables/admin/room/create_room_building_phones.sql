CREATE TABLE `RoomBuildingPhones` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `buildingId` int(11) NOT NULL,
  `phone` varchar(64) NOT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_RoomBuildingPhones_buildingId_idx` (`buildingId`),
  CONSTRAINT `fk_RoomBuildingPhones_buildingId` FOREIGN KEY (`buildingId`) REFERENCES `RoomBuilding` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);