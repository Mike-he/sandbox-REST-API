CREATE TABLE `RoomBuildingCompany` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `buildingId` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `website` varchar(255) NOT NULL,
  `phone` varchar(64) NOT NULL,
  `email` varchar(255) NOT NULL,
  `comment` longtext NOT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `buildingId_idx` (`buildingId`),
  CONSTRAINT `fk_buildingId_idx` FOREIGN KEY (`buildingId`) REFERENCES `RoomBuilding` (`id`) ON DELETE CASCADE
);