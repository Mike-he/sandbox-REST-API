CREATE TABLE `RoomFloor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `floorNumber` int(11) NOT NULL,
  `buildingId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_Floor_buildingId_idx` (`buildingId`),
  UNIQUE KEY `floorNumber_buildingId UNIQUE` (`floorNumber`,`buildingId`),
  CONSTRAINT `fk_Floor_buildingId` FOREIGN KEY (`buildingId`) REFERENCES `Building` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);
