CREATE TABLE `OrderMap` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('product','recharge','upgrade') NOT NULL,
  `orderId` int(11) NOT NULL,
  `chargeId` varchar(128) UNIQUE,
  PRIMARY KEY (`id`),
);