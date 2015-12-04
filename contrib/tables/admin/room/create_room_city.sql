CREATE TABLE `RoomCity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `key` varchar(16) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_UNIQUE` (`key`)
);

INSERT INTO `RoomCity`(`name`, `key`) VALUES('上海(Shanghai)', 'sh');
INSERT INTO `RoomCity`(`name`, `key`) VALUES('北京(Beijing)', 'bj');
INSERT INTO `RoomCity`(`name`, `key`) VALUES('广州(Guangzhou)', 'gz');
INSERT INTO `RoomCity`(`name`, `key`) VALUES('深圳(Shenzhen)', 'sz');
INSERT INTO `RoomCity`(`name`, `key`) VALUES('厦门(Xiamen)', 'xm');
INSERT INTO `RoomCity`(`name`, `key`) VALUES('杭州(Hangzhou)', 'hz');
INSERT INTO `RoomCity`(`name`, `key`) VALUES('成都(Chengdu)', 'cd');
INSERT INTO `RoomCity`(`name`, `key`) VALUES('重庆(Chongqing)', 'cq');
INSERT INTO `RoomCity`(`name`, `key`) VALUES('青岛(Qingdao)', 'qd');
INSERT INTO `RoomCity`(`name`, `key`) VALUES("西安(Xi'an)", 'xa');
INSERT INTO `RoomCity`(`name`, `key`) VALUES('旧金山(San Francisco)', 'sf');
INSERT INTO `RoomCity`(`name`, `key`) VALUES('波士顿(Boston)', 'bs');
