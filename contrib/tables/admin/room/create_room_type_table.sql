CREATE TABLE `RoomTypes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(16) NOT NULL,
  PRIMARY KEY (`id`)
);

INSERT INTO `RoomTypes` (`name`, `icon`) VALUES('office', 'https://image.sandbox3.cn/icon/room_type_office.png');
INSERT INTO `RoomTypes` (`name`, `icon`) VALUES('meeting', 'https://image.sandbox3.cn/icon/room_type_meeting.png');
INSERT INTO `RoomTypes` (`name`, `icon`) VALUES('flexible', 'https://image.sandbox3.cn/icon/room_type_flexible.png');
INSERT INTO `RoomTypes` (`name`, `icon`) VALUES('fixed', 'https://image.sandbox3.cn/icon/room_type_fixed.png');
INSERT INTO `RoomTypes` (`name`, `icon`) VALUES('studio', 'https://image.sandbox3.cn/icon/room_type_studio.png');
INSERT INTO `RoomTypes` (`name`, `icon`) VALUES('space', 'https://image.sandbox3.cn/icon/room_type_space.png');
INSERT INTO `RoomTypes` (`name`, `icon`) VALUES('video_studio', 'https://image.sandbox3.cn/icon/room_type_video_studio.png');
