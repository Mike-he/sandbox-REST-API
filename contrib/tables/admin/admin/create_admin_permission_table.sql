CREATE TABLE `AdminPermission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `typeId` int(11) NOT NULL,
  `key` varchar(16) NOT NULL,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_UNIQUE` (`key`),
  KEY `fk_AdminPermission_typeId_idx` (`typeId`),
  CONSTRAINT `fk_AdminPermission_typeId` FOREIGN KEY (`typeId`) REFERENCES `AdminType` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);
