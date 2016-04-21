CREATE TABLE `ShopAdminPermission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(32) NOT NULL,
  `name` varchar(64) NOT NULL,
  `typeId` int(11) NOT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_UNIQUE` (`key`),
  KEY `IDX_E9E621569BF49490` (`typeId`),
  CONSTRAINT `FK_E9E621569BF49490` FOREIGN KEY (`typeId`) REFERENCES `ShopAdminType` (`id`) ON DELETE CASCADE
);

INSERT INTO ShopAdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'shop.platform.dashboard','控制台管理','2016-03-01 00:00:00','2016-03-01 00:00:00');
INSERT INTO ShopAdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'shop.platform.admin','管理员管理','2016-03-01 00:00:00','2016-03-01 00:00:00');
INSERT INTO ShopAdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'shop.platform.shop','商店新增','2016-03-01 00:00:00','2016-03-01 00:00:00');
INSERT INTO ShopAdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'shop.platform.spec','规格管理','2016-03-01 00:00:00','2016-03-01 00:00:00');
INSERT INTO ShopAdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'shop.shop.shop','商店管理','2016-03-01 00:00:00','2016-03-01 00:00:00');
INSERT INTO ShopAdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'shop.shop.order','订单管理','2016-03-01 00:00:00','2016-03-01 00:00:00');
INSERT INTO ShopAdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'shop.shop.product','商品管理','2016-03-01 00:00:00','2016-03-01 00:00:00');
INSERT INTO ShopAdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'shop.shop.kitchen','传菜系统管理','2016-03-01 00:00:00','2016-03-01 00:00:00');