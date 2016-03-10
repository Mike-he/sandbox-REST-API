CREATE TABLE `SalesAdminPermissionMap` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `adminId` int(11) NOT NULL,
  `permissionId` int(11) NOT NULL,
  `opLevel` int(11) NOT NULL,
  `buildingId` int(11) DEFAULT NULL,
  `creationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `adminId_permissionId_buildingId_UNIQUE` (`adminId`,`permissionId`,`buildingId`),
  KEY `fk_SalesAdminPermissionMap_adminId_idx` (`adminId`),
  KEY `fk_SalesAdminPermissionMap_permissionId_idx` (`permissionId`),
  KEY `IDX_4771ECD5F55CF348` (`buildingId`),
  CONSTRAINT `fk_SalesAdminPermissionMap_adminId` FOREIGN KEY (`adminId`) REFERENCES `SalesAdmin` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_SalesAdminPermissionMap_permissionId` FOREIGN KEY (`permissionId`) REFERENCES `SalesAdminPermission` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);