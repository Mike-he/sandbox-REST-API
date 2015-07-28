CREATE TABLE `AdminPermissionMap` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `adminId` int(11) NOT NULL,
  `permissionId` int(11) NOT NULL,
  `opLevel` tinyint(4) NOT NULL,
  `creationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `adminId_permissionId_UNIQUE` (`adminId`,`permissionId`),
  KEY `fk_AdminPermissionMap_adminId_idx` (`adminId`),
  KEY `fk_AdminPermissionMap_permissionId_idx` (`permissionId`),
  CONSTRAINT `fk_AdminPermissionMap_adminId` FOREIGN KEY (`adminId`) REFERENCES `Admin` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_AdminPermissionMap_permissionId` FOREIGN KEY (`permissionId`) REFERENCES `AdminPermission` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);
