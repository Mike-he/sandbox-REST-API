CREATE TABLE `Supplies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_UNIQUE` (`name`)
);

INSERT INTO Supplies (name) VALUES ('液晶电视');
INSERT INTO Supplies (name) VALUES ('投影仪');
INSERT INTO Supplies (name) VALUES ('白板');
INSERT INTO Supplies (name) VALUES ('电子白板');
INSERT INTO Supplies (name) VALUES ('电话会议设备');
INSERT INTO Supplies (name) VALUES ('视频会议设备');
INSERT INTO Supplies (name) VALUES ('音响及扩音设备');
INSERT INTO Supplies (name) VALUES ('苹果无线投影');
INSERT INTO Supplies (name) VALUES ('其它无线投影');
INSERT INTO Supplies (name) VALUES ('无线网络');
INSERT INTO Supplies (name) VALUES ('有线网路');
INSERT INTO Supplies (name) VALUES ('咖啡茶水');
INSERT INTO Supplies (name) VALUES ('茶歇小食');
INSERT INTO Supplies (name) VALUES ('打印复印');


