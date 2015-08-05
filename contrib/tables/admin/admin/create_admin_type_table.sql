CREATE TABLE `AdminType` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(32) NOT NULL,
  `name` varchar(64) NOT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_UNIQUE` (`key`)
);

INSERT INTO AdminType(`key`,`name`,`creationDate`,`modificationDate`) VALUES('super','Super Admin','2015-08-03 11:00:00','2015-08-3 11:00:00');
INSERT INTO AdminType(`key`,`name`,`creationDate`,`modificationDate`) VALUES('platform','Platform Admin','2015-08-03 11:00:00','2015-08-3 11:00:00');