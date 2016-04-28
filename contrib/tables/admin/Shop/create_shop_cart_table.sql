CREATE TABLE `ShopMenu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shopId` int(11) NOT NULL,
  `cartInfo` text NOT NULL,
  `creationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
);