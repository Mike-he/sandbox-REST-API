CREATE TABLE `RoomAttachment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roomId` int(11) NOT NULL,
  `content` text NOT NULL,
  `attachmentType` varchar(64) NOT NULL,
  `filename` varchar(64) NOT NULL,
  `preview` text,
  `size` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_RoomAttachment_roomId_idx` (`roomId`),
  CONSTRAINT `fk_RoomAttachment_roomId` FOREIGN KEY (`roomId`) REFERENCES `Room` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);