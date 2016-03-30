CREATE TABLE `ShopAdminPermissionMap` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `adminId` int(11) NOT NULL,
  `permissionId` int(11) NOT NULL,
  `creationDate` datetime NOT NULL,
  `opLevel` int(11) NOT NULL,
  `shopId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `adminId_permissionId_buildingId_UNIQUE` (`adminId`,`permissionId`,`shopId`),
  KEY `IDX_627EC1E605405B0` (`permissionId`),
  KEY `IDX_627EC1E2D696931` (`adminId`),
  KEY `fk_AdminPermissionMap_shopId_idx` (`shopId`),
  CONSTRAINT `FK_627EC1E2D696931` FOREIGN KEY (`adminId`) REFERENCES `ShopAdmin` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_627EC1E605405B0` FOREIGN KEY (`permissionId`) REFERENCES `ShopAdminPermission` (`id`) ON DELETE CASCADE
);