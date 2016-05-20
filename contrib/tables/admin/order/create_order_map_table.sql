CREATE TABLE `OrderMap` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('product','topup','upgrade', 'event') NOT NULL,
  `orderNumber` varchar(64),
  `orderId` int(11),
  `chargeId` varchar(128) UNIQUE,
  PRIMARY KEY (`id`)
);