CREATE TABLE `ShopAttachment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` longtext  NOT NULL,
  `attachmentType` varchar(64) NOT NULL,
  `filename` varchar(64) NOT NULL,
  `preview` longtext DEFAULT NULL,
  `size` int(11) NOT NULL,
  `shopId` int(11) NOT NULL,
  `creationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `shopId_idx` (`shopId`),
  CONSTRAINT `fk_shopId_idx` FOREIGN KEY (`shopId`) REFERENCES `Shop` (`id`) ON DELETE CASCADE
);