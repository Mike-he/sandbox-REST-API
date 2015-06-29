CREATE TABLE `ProductOrder` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `productId` int(11),
  `startDate` datetime NOT NULL,
  `endDate` datetime NOT NULL,
  `price` numeric(15,2),
  `status` enum('paid','unpaid','completed','cancelled') DEFAULT NULL,
  `paymentDate` datetime,
  `cancelledDate` datetime,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_ProductOrder_userId_idx` (`userId`),
  KEY `fk_ProductOrder_productId_idx` (`productId`),
  CONSTRAINT `fk_ProductOrder_userId` FOREIGN KEY (`userId`) REFERENCES `User` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_ProductOrder_productId` FOREIGN KEY (`productId`) REFERENCES `Product` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);