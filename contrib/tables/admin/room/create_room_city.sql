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

INSERT INTO `RoomCity`(`name`, `key`) VALUES('大连(Dalian)', 'dl');
INSERT INTO `RoomCity`(`name`, `key`) VALUES('嘉兴(Jiaxing)', 'jx');
INSERT INTO `RoomCity`(`name`, `key`) VALUES('南京(Nanjing)', 'nj');
INSERT INTO `RoomCity`(`name`, `key`) VALUES('珠海(Zhuhai)', 'zh');
INSERT INTO `RoomCity`(`name`, `key`) VALUES('南宁(Nanning)', 'nn');
INSERT INTO `RoomCity`(`name`, `key`) VALUES('天津(Tianjin)', 'tj');
INSERT INTO `RoomCity`(`name`, `key`) VALUES('佛山(Foshan)', 'fs');
INSERT INTO `RoomCity`(`name`, `key`) VALUES('武汉(Wuhan)', 'wh');
INSERT INTO `RoomCity`(`name`, `key`) VALUES('昆明(Kunming)', 'km');
INSERT INTO `RoomCity`(`name`, `key`) VALUES('烟台(Yantai)', 'yt');
INSERT INTO `RoomCity`(`name`, `key`) VALUES('泉州(Quanzhou)', 'qz');
INSERT INTO `RoomCity`(`name`, `key`) VALUES('太原(Taiyuan)', 'ty');
INSERT INTO `RoomCity`(`name`, `key`) VALUES('宁波(Ningbo)', 'nb');
INSERT INTO `RoomCity`(`name`, `key`) VALUES('福州(Fuzhou)', 'fz');
INSERT INTO `RoomCity`(`name`, `key`) VALUES('廊坊(Langfang)', 'lf');
INSERT INTO `RoomCity`(`name`, `key`) VALUES('绍兴(Shaoxing)', 'sx');
INSERT INTO `RoomCity`(`name`, `key`) VALUES('苏州(Suzhou)', 'suz');
