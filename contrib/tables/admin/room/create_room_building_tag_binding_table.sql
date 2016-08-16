CREATE TABLE `RoomBuildingTagBinding` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `creationDate` datetime NOT NULL,
  `buildingId` int(11) NOT NULL,
  `tagId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `buildingId_idx` (`buildingId`),
  KEY `tagId_idx` (`tagId`),
  CONSTRAINT `fk_tagId_idx` FOREIGN KEY (`tagId`) REFERENCES `RoomBuildingTag` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_buildingId_idx` FOREIGN KEY (`buildingId`) REFERENCES `RoomBuilding` (`id`) ON DELETE CASCADE
);