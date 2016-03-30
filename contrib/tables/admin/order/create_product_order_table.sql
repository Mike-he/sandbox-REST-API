CREATE TABLE `ProductOrder` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderNumber` varchar(128) NOT NULL,
  `payChannel` varchar(16),
  `userId` int(11) NOT NULL,
  `productId` int(11),
  `startDate` datetime NOT NULL,
  `endDate` datetime NOT NULL,
  `price` numeric(15,2) NOT NULL,
  `discountPrice` numeric(15,2) NOT NULL,
  `status` enum('paid','unpaid','completed','cancelled') DEFAULT NULL,
  `location` text,
  `isRenew` boolean DEFAULT FALSE NOT NULL,
  `paymentDate` datetime,
  `cancelledDate` datetime,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  `appointedPerson` int(11),
  `ruleId` int(11),
  `ruleName` varchar(255),
  `ruleDescription` varchar(255),
  `membershipBindId` int(11),
  `adminId` int(11),
  `type` VARCHAR(64),
  PRIMARY KEY (`id`),
  KEY `fk_ProductOrder_userId_idx` (`userId`),
  KEY `fk_ProductOrder_productId_idx` (`productId`),
  CONSTRAINT `fk_ProductOrder_userId` FOREIGN KEY (`userId`) REFERENCES `User` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_ProductOrder_productId` FOREIGN KEY (`productId`) REFERENCES `Product` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);