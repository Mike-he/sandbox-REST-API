CREATE TABLE `AdminType` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(32) NOT NULL,
  `name` varchar(64) NOT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_UNIQUE` (`key`)
);

INSERT INTO AdminType(`key`,`name`,`creationDate`,`modificationDate`) VALUES('super','超级管理员','2015-08-24 00:00:00','2015-08-24 00:00:00');
INSERT INTO AdminType(`key`,`name`,`creationDate`,`modificationDate`) VALUES('platform','平台管理员','2015-08-24 00:00:00','2015-08-24 00:00:00');
