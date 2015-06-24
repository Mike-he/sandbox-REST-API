CREATE TABLE `RoomMeeting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roomId` int(11) NOT NULL,
  `startHour` TIME NOT NULL,
  `endHour` TIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_RoomMeeting_roomId_idx` (`roomId`),
  UNIQUE KEY `roomId_UNIQUE` (`roomId`),
  CONSTRAINT `fk_RoomMeeting_date_roomId` FOREIGN KEY (`roomId`) REFERENCES `Room` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);