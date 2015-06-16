CREATE TABLE `AdminPermissionMap` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL,
  `permissionId` int(11) NOT NULL,
  `creationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_AdminPermissionMap_username_idx` (`username`),
  KEY `fk_AdminPermissionMap_permissionId_idx` (`permissionId`),
  CONSTRAINT `fk_AdminPermissionMap_username` FOREIGN KEY (`username`) REFERENCES `Admin` (`username`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_AdminPermissionMap_permissionId` FOREIGN KEY (`permissionId`) REFERENCES `AdminPermission` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);
