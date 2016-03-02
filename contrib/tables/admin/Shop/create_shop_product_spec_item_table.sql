CREATE TABLE `ShopProductSpecItem` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `productSpecId` int(11) NOT NULL,
  `price` DECIMAL DEFAULT NULL,
  `inventory` int(11) DEFAULT NULL,
  `shopSpecItemId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `productSpecId_shopSpecItemId_UNIQUE` (`productSpecId`,`shopSpecItemId`),
  KEY `fk_ShopProductSpecItem_productSpecId_idx` (`productSpecId`),
  KEY `fk_ShopProductSpecItem_shopSpecItemId_idx` (`shopSpecItemId`),
  CONSTRAINT `fk_ShopProductSpecItem_productSpecId` FOREIGN KEY (`productSpecId`) REFERENCES `ShopProductSpec` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_ShopProductSpecItem_shopSpecItemId` FOREIGN KEY (`shopSpecItemId`) REFERENCES `ShopSpecItem` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
);