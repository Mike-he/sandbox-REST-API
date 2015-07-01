CREATE TABLE `RoomMeeting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roomId` int(11) NOT NULL,
  `startHour` time DEFAULT NULL,
  `endHour` time DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_RoomMeeting_roomId_idx` (`roomId`),
  CONSTRAINT `fk_RoomMeeting_roomId` FOREIGN KEY (`roomId`) REFERENCES `Room` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);