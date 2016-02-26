CREATE TABLE `ShopProductSpecItem` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `productSpecId` int(11) NOT NULL,
  `price` DECIMAL DEFAULT 0,
  `inventory` int(11) DEFAULT 0,
  `shopSpecItemId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_ShopProductSpecItem_productSpecId_idx` (`productSpecId`),
  CONSTRAINT `fk_ShopProductSpecItem_productSpecId` FOREIGN KEY (`productSpecId`) REFERENCES `ShopProductSpec` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);