CREATE TABLE `ShopMenu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  `count` int(11) NOT NULL,
  `sortTime` varchar(15) NOT NULL,
  `creationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_ShopMenu_shopId_idx` (`shopId`),
  CONSTRAINT `fk_Shop_shopId` FOREIGN KEY (`shopId`) REFERENCES `Shop` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);