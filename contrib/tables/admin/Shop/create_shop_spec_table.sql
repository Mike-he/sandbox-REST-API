CREATE TABLE `ShopSpec` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  `description` text,
  `multiple` boolean DEFAULT FALSE,
  `optional` boolean DEFAULT FALSE,
  PRIMARY KEY (`id`),
  KEY `fk_ShopSpec_shopId_idx` (`shopId`),
  CONSTRAINT `fk_ShopSpec_shopId` FOREIGN KEY (`shopId`) REFERENCES `Shop` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);