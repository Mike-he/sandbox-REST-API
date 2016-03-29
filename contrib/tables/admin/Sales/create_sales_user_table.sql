CREATE TABLE `SalesUser` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `companyId` int(11) NOT NULL,
  `buildingId` int(11) NOT NULL,
  `creationDate` datetime NOT NULL,
  PRIMARY KEY (`id`)
);