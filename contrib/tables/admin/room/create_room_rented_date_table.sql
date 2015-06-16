CREATE TABLE `RoomRentedDate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roomId` int(11) NOT NULL,
  `startDate` DATETIME NOT NULL, #think about this or only the rented day
  `endDate` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_RoomRentedDate_roomId_idx` (`roomId`),
  CONSTRAINT `fk_RoomRentedDate_roomId` FOREIGN KEY (`roomId`) REFERENCES `Room` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);