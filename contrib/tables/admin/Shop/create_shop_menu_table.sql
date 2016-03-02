CREATE TABLE `ShopMenu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  `sortTime` varchar(15) NOT NULL,
  `invisible` boolean DEFAULT FALSE,
  `creationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shopId_name_UNIQUE` (`shopId`,`name`),
  KEY `fk_ShopMenu_shopId_idx` (`shopId`),
  CONSTRAINT `fk_ShopMenu_shopId` FOREIGN KEY (`shopId`) REFERENCES `Shop` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);