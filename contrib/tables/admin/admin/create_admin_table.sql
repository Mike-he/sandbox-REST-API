CREATE TABLE `Admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL,
  `password` varchar(256) NOT NULL,
  `name` varchar(128) DEFAULT NULL,
  `typeKey` varchar(16) NOT NULL,
  `creationDate` char(15) NOT NULL,
  `modificationDate` char(15) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username_UNIQUE` (`username`),
  KEY `fk_Admin_typeKey_idx` (`typeKey`),
  CONSTRAINT `fk_Admin_typeKey` FOREIGN KEY (`typeKey`) REFERENCES `AdminType` (`key`) ON DELETE NO ACTION ON UPDATE NO ACTION
);
