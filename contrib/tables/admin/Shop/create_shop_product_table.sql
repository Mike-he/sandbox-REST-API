CREATE TABLE `ShopProduct` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `description` text,
  `menuId` int(11) NOT NULL,
  `sortTime` varchar(15) NOT NULL,
  `online` boolean DEFAULT FALSE NOT NULL,
  `invisible` boolean DEFAULT FALSE NOT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `menuId_name_UNIQUE` (`menuId`,`name`),
  KEY `fk_ShopProduct_menuId_idx` (`menuId`),
  CONSTRAINT `fk_ShopProduct_menuId` FOREIGN KEY (`menuId`) REFERENCES `ShopMenu` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);