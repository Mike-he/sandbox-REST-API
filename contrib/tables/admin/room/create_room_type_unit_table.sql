CREATE TABLE `RoomTypeUnit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `typeId` int(11) NOT NULL,
  `unit` VARCHAR(16) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_RoomTypeUnit_typeId_idx` (`typeId`),
  CONSTRAINT `fk_RoomTypeUnit_typeId` FOREIGN KEY (`typeId`) REFERENCES `RoomTypes` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);

INSERT INTO RoomTypeUnit(`typeId`,`unit`) VALUES(7,'month');
INSERT INTO RoomTypeUnit(`typeId`,`unit`) VALUES(8,'hour');
INSERT INTO RoomTypeUnit(`typeId`,`unit`) VALUES(9,'day');
INSERT INTO RoomTypeUnit(`typeId`,`unit`) VALUES(10,'day');
INSERT INTO RoomTypeUnit(`typeId`,`unit`) VALUES(10,'month');
INSERT INTO RoomTypeUnit(`typeId`,`unit`) VALUES(11,'hour');
INSERT INTO RoomTypeUnit(`typeId`,`unit`) VALUES(12,'hour');