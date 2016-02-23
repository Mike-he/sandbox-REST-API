CREATE TABLE `ShopSpecItem` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `specId` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  `amount` int(11) DEFAULT 0,
  `price` DECIMAL DEFAULT 0,
  `inventory` boolean DEFAULT FALSE,
  PRIMARY KEY (`id`),
  KEY `fk_ShopSpecItem_specId_idx` (`specId`),
  CONSTRAINT `fk_ShopSpecItem_specId` FOREIGN KEY (`specId`) REFERENCES `ShopSpec` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);