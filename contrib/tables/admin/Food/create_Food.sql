CREATE TABLE `Food` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `cityId` int(11) NOT NULL,
  `buildingId` int(11) NOT NULL,
  `type` enum('drinks','desserts') NOT NULL,
  `price` numeric(15,2),
  `inventory` int(11),
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_Food_cityId_idx` (`cityId`),
  KEY `fk_Food_buildingId_idx` (`buildingId`),
  CONSTRAINT `fk_Food_buildingId` FOREIGN KEY (`buildingId`) REFERENCES `RoomBuilding` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_Food_cityId` FOREIGN KEY (`cityId`) REFERENCES `RoomCity` (`id`) ON DELETE CASCADE
);