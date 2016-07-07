CREATE TABLE `RoomType` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` enum('office','meeting','flexible','fixed','studio') NOT NULL,
  PRIMARY KEY (`id`)
);

INSERT INTO RoomType(`name`) VALUES('office');
INSERT INTO RoomType(`name`) VALUES('meeting');
INSERT INTO RoomType(`name`) VALUES('flexible');
INSERT INTO RoomType(`name`) VALUES('fixed');
INSERT INTO RoomType(`name`) VALUES('studio');