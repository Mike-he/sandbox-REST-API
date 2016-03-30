CREATE TABLE `ShopOrderProductSpec` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `specId` int(11) NOT NULL,
  `productId` int(11) NOT NULL,
  `shopProductSpecInfo` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `specId_productId_UNIQUE` (`specId`,`productId`),
  KEY `fk_ShopOrderProductSpec_specId_idx` (`specId`),
  KEY `fk_ShopOrderProductSpec_productId_idx` (`productId`),
  CONSTRAINT `fk_ShopOrderProductSpec_specId` FOREIGN KEY (`specId`) REFERENCES `ShopProductSpec` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_ShopOrderProductSpec_productId` FOREIGN KEY (`productId`) REFERENCES `ShopOrderProduct` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);