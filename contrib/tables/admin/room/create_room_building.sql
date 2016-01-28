CREATE TABLE `RoomBuilding` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(1024) DEFAULT NULL,
  `detail` LONGTEXT NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `address` VARCHAR(255) NOT NULL,
  `lat` FLOAT(9,6) NOT NULL,
  `lng` FLOAT(9,6) NOT NULL,
  `cityId` int(11) NOT NULL,
  `server` VARCHAR(255) DEFAULT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `businessHour` VARCHAR(255) DEFAULT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_Building_cityId_idx` (`cityId`),
  CONSTRAINT `fk_Building_cityId` FOREIGN KEY (`cityId`) REFERENCES `RoomCity` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);

INSERT INTO `RoomBuilding`(`name`,`cityId`,`address`,`lat`,`lng`,`avatar`,`server`) VALUES('环球国际金融中心',3,'上海市浦东新区世纪大道1000号',121.514402,31.24046,'TDB','TDB');
INSERT INTO `RoomBuilding`(`name`,`cityId`,`address`,`lat`,`lng`,`avatar`,`server`) VALUES('上海Sandbox',3,'上海市浦东新区祖冲之路2290弄5号',121.632682,31.216193,'TDB','TDB');
INSERT INTO `RoomBuilding`(`name`,`cityId`,`address`,`lat`,`lng`,`avatar`,`server`) VALUES('展想广场',3,'上海市浦东新区祖冲之路2290弄1号',121.63521,31.216629,'TDB','TDB');
INSERT INTO `RoomBuilding`(`name`,`cityId`,`address`,`lat`,`lng`,`avatar`,`server`) VALUES('清华科技园',4,'中关村东路1号',116.355218,40.02273,'TDB','TDB');
INSERT INTO `RoomBuilding`(`name`,`cityId`,`address`,`lat`,`lng`,`avatar`,`server`) VALUES('北京Sandbox',4,'朝阳区工体北路13号3号楼',116.466435,39.94007,'TDB','TDB');
INSERT INTO `RoomBuilding`(`name`,`cityId`,`address`,`lat`,`lng`,`avatar`,`server`) VALUES('三里屯环宇',4,'西直门外大街137号',116.342397,39.947082,'TDB','TDB');
INSERT INTO `RoomBuilding`(`name`,`cityId`,`address`,`lat`,`lng`,`avatar`,`server`) VALUES('广州塔',5,'广州市海珠区阅江西路222号',113.331348,23.111991,'TDB','TDB');
INSERT INTO `RoomBuilding`(`name`,`cityId`,`address`,`lat`,`lng`,`avatar`,`server`) VALUES('广州Sandbox',5,'天河区华利路61号',113.326987,23.123707,'TDB','TDB');
INSERT INTO `RoomBuilding`(`name`,`cityId`,`address`,`lat`,`lng`,`avatar`,`server`) VALUES('摩天楼',5,'荔湾区光复中路315号',113.257925,23.125015,'TDB','TDB');
