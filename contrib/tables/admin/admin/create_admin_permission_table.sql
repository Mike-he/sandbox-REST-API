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

INSERT INTO AdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'platform.order','Platform Order Management','2015-08-05 00:00:00','2015-08-05 00:00:00');
INSERT INTO AdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'platform.user','Platform User Management','2015-08-05 00:00:00','2015-08-05 00:00:00');
INSERT INTO AdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'platform.room','Platform Room Management','2015-08-05 00:00:00','2015-08-05 00:00:00');
INSERT INTO AdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'platform.product','Platform Product Management','2015-08-05 00:00:00','2015-08-05 00:00:00');
INSERT INTO AdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'platform.price','Platform Price Management','2015-08-05 00:00:00','2015-08-05 00:00:00');
INSERT INTO AdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'platform.access','Platform Access Management','2015-08-05 00:00:00','2015-08-05 00:00:00');
INSERT INTO AdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'platform.admin','Platform Admin Management','2015-08-05 00:00:00','2015-08-05 00:00:00');
INSERT INTO AdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'platform.announcement','Platform Announcement Management','2015-08-05 00:00:00','2015-08-05 00:00:00');
INSERT INTO AdminPermission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`) VALUES(2,'platform.dashboard','Platform Dashboard Management','2015-08-05 00:00:00','2015-08-05 00:00:00');