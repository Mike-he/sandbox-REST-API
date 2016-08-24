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
INSERT INTO AdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'platform.admin','管理员管理','2015-08-24 00:00:00','2015-08-24 00:00:00');
INSERT INTO AdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'platform.announcement','通知管理','2015-08-24 00:00:00','2015-08-24 00:00:00');
INSERT INTO AdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'platform.dashboard','控制台管理','2015-08-24 00:00:00','2015-08-24 00:00:00');
INSERT INTO AdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'platform.event','活动管理','2015-08-24 00:00:00','2015-08-24 00:00:00');
INSERT INTO AdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'platform.banner','横幅管理','2015-08-24 00:00:00','2015-08-24 00:00:00');
INSERT INTO AdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'platform.news','新闻管理','2015-08-24 00:00:00','2015-08-24 00:00:00');
INSERT INTO AdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'platform.message','消息管理','2015-08-24 00:00:00','2015-08-24 00:00:00');
INSERT INTO AdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'platform.verify','审查管理','2015-08-24 00:00:00','2015-08-24 00:00:00');
INSERT INTO AdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'platform.sales','销售方管理','2015-08-24 00:00:00','2015-08-24 00:00:00');
INSERT INTO AdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'platform.invoice','发票管理','2016-03-01 00:00:00','2016-03-01 00:00:00');
INSERT INTO AdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'platform.access','门禁系统','2015-08-24 00:00:00','2015-08-24 00:00:00');
INSERT INTO AdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'platform.room','空间管理','2015-08-24 00:00:00','2015-08-24 00:00:00');
INSERT INTO AdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'platform.product','商品管理','2015-08-24 00:00:00','2015-08-24 00:00:00');
INSERT INTO AdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'platform.price','价格体系管理','2015-08-24 00:00:00','2015-08-24 00:00:00');
INSERT INTO AdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'platform.building','大楼管理','2015-08-24 00:00:00','2015-08-24 00:00:00');
INSERT INTO AdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'platform.bulletin','公告管理','2015-08-24 00:00:00','2015-08-24 00:00:00');
INSERT INTO AdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'platform.order.reserve','订单预留','2015-08-24 00:00:00','2015-08-24 00:00:00');
INSERT INTO AdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'platform.order.preorder','订单预定','2015-08-24 00:00:00','2015-08-24 00:00:00');
INSERT INTO AdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'platform.product.appointment','预约审核','2016-07-08 00:00:00','2016-07-08 00:00:00');
INSERT INTO AdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'platform.log','日志管理','2016-07-08 00:00:00','2016-07-08 00:00:00');
INSERT INTO AdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'platform.advertising','广告管理','2016-07-08 00:00:00','2016-07-08 00:00:00');