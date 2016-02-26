CREATE TABLE `ShopProductSpec` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `productId` int(11) NOT NULL,
  `shopSpecId` int(11) NOT NULL,
  `optional` boolean DEFAULT FALSE,
  PRIMARY KEY (`id`),
  KEY `fk_ShopProductSpec_productId_idx` (`productId`),
  CONSTRAINT `fk_ShopProductSpec_productId` FOREIGN KEY (`productId`) REFERENCES `ShopProduct` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);