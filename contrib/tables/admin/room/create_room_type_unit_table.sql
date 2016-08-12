CREATE TABLE `RoomTypeUnit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `typeId` int(11) NOT NULL,
  `unit` enum('hour','day','month') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_RoomTypeUnit_typeId_idx` (`typeId`),
  CONSTRAINT `fk_RoomTypeUnit_typeId` FOREIGN KEY (`typeId`) REFERENCES `RoomTypes` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);

INSERT INTO RoomTypeUnit(`typeId`,`unit`) VALUES(1,'month');
INSERT INTO RoomTypeUnit(`typeId`,`unit`) VALUES(2,'hour');
INSERT INTO RoomTypeUnit(`typeId`,`unit`) VALUES(3,'day');
INSERT INTO RoomTypeUnit(`typeId`,`unit`) VALUES(4,'day');
INSERT INTO RoomTypeUnit(`typeId`,`unit`) VALUES(4,'month');
INSERT INTO RoomTypeUnit(`typeId`,`unit`) VALUES(5,'hour');
INSERT INTO RoomTypeUnit(`typeId`,`unit`) VALUES(6,'hour');