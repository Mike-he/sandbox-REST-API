CREATE TABLE `ShopOrder` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL,
  `orderNumber` varchar(255), NOT NULL,
  `payChannel` varchar(16),
  `userId` int(11) NOT NULL,
  `price` DECIMAL PRECISION 10 SCALE 2 NOT NULL,
  `status` varchar(64) NOT NULL,
  `paymentDate` datetime NOT NULL,
  `cancelledDate` datetime NOT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  `linkedOrderId` int(11),
  PRIMARY KEY (`id`),
  UNIQUE KEY `orderNumber_UNIQUE` (`orderNumber`),
  KEY `fk_ShopOrder_shopId_idx` (`shopId`),
  CONSTRAINT `fk_ShopOrder_shopId` FOREIGN KEY (`shopId`) REFERENCES `Shop` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);