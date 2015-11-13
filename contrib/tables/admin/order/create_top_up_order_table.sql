CREATE TABLE `TopUpOrder` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderNumber` varchar(128) NOT NULL,
  `payChannel` varchar(16),
  `userId` int(11) NOT NULL,
  `price` numeric(15,2),
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  PRIMARY KEY (`id`)
);