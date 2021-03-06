CREATE TABLE `SalesUser` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `shopId` int(11),
  `companyId` int(11) NOT NULL,
  `buildingId` int(11) NOT NULL,
  `isOrdered` tinyint(1) NOT NULL,
  `isShopOrdered` tinyint(1) NOT NULL,
  `isAuthorized` tinyint(1) NOT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  PRIMARY KEY (`id`)
);