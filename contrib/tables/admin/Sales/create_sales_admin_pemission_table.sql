CREATE TABLE `SalesAdminPermission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `typeId` int(11) NOT NULL,
  `key` varchar(32) NOT NULL,
  `name` varchar(64) NOT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_UNIQUE` (`key`),
  KEY `fk_SalesAdminPermission_typeId_idx` (`typeId`),
  CONSTRAINT `fk_SalesAdminPermission_typeId` FOREIGN KEY (`typeId`) REFERENCES `SalesAdminType` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);

INSERT INTO SalesAdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'sales.platform.admin','管理员管理','2016-03-01 00:00:00','2016-03-01 00:00:00');
INSERT INTO SalesAdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'sales.platform.building','项目管理','2016-03-01 00:00:00','2016-03-01 00:00:00');
INSERT INTO SalesAdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'sales.building.price','价格模板管理','2016-03-01 00:00:00','2016-03-01 00:00:00');
INSERT INTO SalesAdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'sales.building.order','订单管理','2016-03-01 00:00:00','2016-03-01 00:00:00');
INSERT INTO SalesAdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'sales.building.order.reserve','订单预留','2016-03-01 00:00:00','2016-03-01 00:00:00');
INSERT INTO SalesAdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'sales.building.building','项目管理','2016-03-01 00:00:00','2016-03-01 00:00:00');
INSERT INTO SalesAdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'sales.building.user','用户管理','2016-03-01 00:00:00','2016-03-01 00:00:00');
INSERT INTO SalesAdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'sales.building.room','空间管理','2016-03-01 00:00:00','2016-03-01 00:00:00');
INSERT INTO SalesAdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'sales.building.product','商品管理','2016-03-01 00:00:00','2016-03-01 00:00:00');
INSERT INTO SalesAdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'sales.building.access','门禁管理','2016-03-01 00:00:00','2016-03-01 00:00:00');
