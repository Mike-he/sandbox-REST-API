CREATE TABLE `SalesAdminType` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(32) NOT NULL,
  `name` varchar(64) NOT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_UNIQUE` (`key`)
);

INSERT INTO SalesAdminType(`key`,`name`,`creationDate`,`modificationDate`) VALUES('super','超级管理员','2016-03-01 00:00:00','2016-03-01 00:00:00');
INSERT INTO SalesAdminType(`key`,`name`,`creationDate`,`modificationDate`) VALUES('platform','平台管理员','2016-03-01 00:00:00','2016-03-01 00:00:00');
