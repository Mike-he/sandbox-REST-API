CREATE TABLE `RoomBuildingAttachment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` longtext  NOT NULL,
  `attachmentType` varchar(64) NOT NULL,
  `filename` varchar(64) NOT NULL,
  `preview` longtext,
  `size` int(11) NOT NULL,
  `buildingId` int(11) NOT NULL,
  `creationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `buildingId_idx` (`buildingId`),
  CONSTRAINT `fk_buildingId_idx` FOREIGN KEY (`buildingId`) REFERENCES `RoomBuilding` (`id`) ON DELETE CASCADE
);