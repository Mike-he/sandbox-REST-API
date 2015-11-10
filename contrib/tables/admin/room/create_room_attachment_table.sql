CREATE TABLE `RoomAttachment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` text NOT NULL,
  `attachmentType` varchar(64) NOT NULL,
  `filename` varchar(64) NOT NULL,
  `preview` text,
  `size` int(11) NOT NULL,
  `roomType` varchar(255) NOT NULL,
  `buildingId` int(11) NOT NULL,
  `creationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_RoomAttachment_buildingId_idx` (`buildingId`),
  CONSTRAINT `fk_RoomAttachment_buildingId` FOREIGN KEY (`buildingId`) REFERENCES `RoomBuilding` (`id`) ON DELETE CASCADE
);