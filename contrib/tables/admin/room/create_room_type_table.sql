CREATE TABLE `RoomTypes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(16) NOT NULL,
  PRIMARY KEY (`id`)
);

INSERT INTO RoomTypes(`name`) VALUES('office');
INSERT INTO RoomTypes(`name`) VALUES('meeting');
INSERT INTO RoomTypes(`name`) VALUES('flexible');
INSERT INTO RoomTypes(`name`) VALUES('fixed');
INSERT INTO RoomTypes(`name`) VALUES('studio');
INSERT INTO RoomTypes(`name`) VALUES('space');