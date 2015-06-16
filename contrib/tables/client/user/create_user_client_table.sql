CREATE TABLE `UserClient` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) DEFAULT NULL,
  `os` varchar(256) DEFAULT NULL,
  `version` varchar(16) DEFAULT NULL,
  `ipAddress` varchar(64) DEFAULT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  PRIMARY KEY (`id`)
);
