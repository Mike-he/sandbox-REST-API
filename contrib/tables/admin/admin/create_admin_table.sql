CREATE TABLE `Admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL,
  `password` varchar(256) NOT NULL,
  `name` varchar(128) DEFAULT NULL,
  `typeId` int(11) NOT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username_UNIQUE` (`username`),
  KEY `fk_Admin_typeId_idx` (`typeId`),
  CONSTRAINT `fk_Admin_typeId` FOREIGN KEY (`typeId`) REFERENCES `AdminType` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
);
