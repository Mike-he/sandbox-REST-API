CREATE TABLE `ShopSpecItem` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `specId` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `specId_name_UNIQUE` (`specId`,`name`),
  KEY `fk_ShopSpecItem_specId_idx` (`specId`),
  CONSTRAINT `fk_ShopSpecItem_specId` FOREIGN KEY (`specId`) REFERENCES `ShopSpec` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);