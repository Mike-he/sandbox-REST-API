CREATE TABLE `ShopOrderProduct` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderId` int(11) NOT NULL,
  `productId` int(11) NOT NULL,
  `shopProductInfo` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_ShopOrderProduct_orderId_idx` (`orderId`),
  KEY `fk_ShopOrderProduct_productId_idx` (`productId`),
  CONSTRAINT `fk_ShopOrderProduct_orderId` FOREIGN KEY (`orderId`) REFERENCES `ShopOrder` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_ShopOrderProduct_productId` FOREIGN KEY (`productId`) REFERENCES `ShopProduct` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
);