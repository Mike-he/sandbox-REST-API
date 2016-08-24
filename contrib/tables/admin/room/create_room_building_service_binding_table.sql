CREATE TABLE `RoomBuildingServiceBinding` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `creationDate` datetime NOT NULL,
  `buildingId` int(11) NOT NULL,
  `serviceId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `buildingId_idx` (`buildingId`),
  KEY `serviceId_idx` (`serviceId`),
  CONSTRAINT `fk_serviceId_idx` FOREIGN KEY (`serviceId`) REFERENCES `RoomBuildingServices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_buildingId_idx` FOREIGN KEY (`buildingId`) REFERENCES `RoomBuilding` (`id`) ON DELETE CASCADE
);