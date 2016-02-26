CREATE TABLE `ShopProductAttachment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` longtext  NOT NULL,
  `attachmentType` varchar(64) NOT NULL,
  `filename` varchar(64) NOT NULL,
  `preview` longtext DEFAULT NULL,
  `size` int(11) NOT NULL,
  `productId` int(11) NOT NULL,
  `creationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_ShopProductAttachment_productId_idx` (`productId`),
  CONSTRAINT `fk_ShopProductAttachment_productId` FOREIGN KEY (`productId`) REFERENCES `ShopProduct` (`id`) ON DELETE CASCADE
);