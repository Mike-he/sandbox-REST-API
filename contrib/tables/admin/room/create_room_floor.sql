CREATE TABLE `RoomFloor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `floorNumber` int(11) NOT NULL,
  `buildingId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `floorNumber_buildingId` (`floorNumber`,`buildingId`),
  KEY `fk_Floor_buildingId_idx` (`buildingId`),
  CONSTRAINT `fk_Floor_buildingId` FOREIGN KEY (`buildingId`) REFERENCES `RoomBuilding` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);
