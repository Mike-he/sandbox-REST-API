CREATE TABLE `ShopOrderProductSpecItem` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `specId` int(11) NOT NULL,
  `itemId` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `shopProductSpecItemInfo` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `specId_itemId_UNIQUE` (`specId`,`itemId`),
  KEY `fk_ShopOrderProductSpecItem_specId_idx` (`specId`),
  KEY `fk_ShopOrderProductSpecItem_productId_idx` (`productId`),
  CONSTRAINT `fk_ShopOrderProductSpecItem_specId` FOREIGN KEY (`specId`) REFERENCES `ShopOrderProductSpec` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_ShopOrderProductSpecItem_itemId` FOREIGN KEY (`itemId`) REFERENCES `ShopProductSpecItem` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
);