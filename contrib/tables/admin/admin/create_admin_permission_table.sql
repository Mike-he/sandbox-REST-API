CREATE TABLE `AdminPermission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `typeId` int(11) NOT NULL,
  `key` varchar(32) NOT NULL,
  `name` varchar(64) NOT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_UNIQUE` (`key`),
  KEY `fk_AdminPermission_typeId_idx` (`typeId`),
  CONSTRAINT `fk_AdminPermission_typeId` FOREIGN KEY (`typeId`) REFERENCES `AdminType` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);

INSERT INTO AdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'platform.order','订单管理','2015-08-24 00:00:00','2015-08-24 00:00:00');
INSERT INTO AdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'platform.user','用户管理','2015-08-24 00:00:00','2015-08-24 00:00:00');
INSERT INTO AdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'platform.room','房间管理','2015-08-24 00:00:00','2015-08-24 00:00:00');
INSERT INTO AdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'platform.product','产品管理','2015-08-24 00:00:00','2015-08-24 00:00:00');
INSERT INTO AdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'platform.price','价格体系管理','2015-08-24 00:00:00','2015-08-24 00:00:00');
INSERT INTO AdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'platform.access','门禁系统管理','2015-08-24 00:00:00','2015-08-24 00:00:00');
INSERT INTO AdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'platform.admin','管理员管理','2015-08-24 00:00:00','2015-08-24 00:00:00');
INSERT INTO AdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'platform.announcement','通知管理','2015-08-24 00:00:00','2015-08-24 00:00:00');
INSERT INTO AdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'platform.dashboard','控制台管理','2015-08-24 00:00:00','2015-08-24 00:00:00');
INSERT INTO AdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'platform.event','活动管理','2015-08-24 00:00:00','2015-08-24 00:00:00');
INSERT INTO AdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'platform.banner','推荐管理','2015-08-24 00:00:00','2015-08-24 00:00:00');
INSERT INTO AdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'platform.news','新闻管理','2015-08-24 00:00:00','2015-08-24 00:00:00');
INSERT INTO AdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'platform.message','消息管理','2015-08-24 00:00:00','2015-08-24 00:00:00');

