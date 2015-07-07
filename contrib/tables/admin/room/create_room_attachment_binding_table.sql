CREATE TABLE `RoomAttachmentBinding` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roomId` int(11) NOT NULL,
  `attachmentId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roomId_attachmentId_UNIQUE` (`roomId`,`attachmentId`),
  KEY `fk_RoomAttachmentBinding_roomId_idx` (`roomId`),
  CONSTRAINT `fk_RoomAttachmentBinding_roomId` FOREIGN KEY (`roomId`) REFERENCES `Room` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);