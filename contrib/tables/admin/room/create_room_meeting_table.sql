CREATE TABLE `RoomMeeting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roomId` int(11) NOT NULL,
  `startHour` DATETIME NOT NULL,
  `endHour` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_RoomMeeting_roomId_idx` (`roomId`),
  CONSTRAINT `fk_RoomMeeting_date_roomId` FOREIGN KEY (`roomId`) REFERENCES `Room` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);