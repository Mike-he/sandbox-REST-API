CREATE TABLE `RoomAttachmentBinding` (
  `roomId` int(11) NOT NULL REFERENCES `Room` (`id`),
  `attachmentId` int(11) NOT NULL REFERENCES `RoomAttachment` (`id`),
  primary key (roomId, attachmentId)
);