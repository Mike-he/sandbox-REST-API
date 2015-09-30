CREATE TABLE `ProductOrderRecord` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderId` int(11) NOT NULL,
  `cityId` int(11) NOT NULL,
  `buildingId` int(11) NOT NULL,
  `roomType` enum('office','meeting','flexible','fixed') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_ProductOrderRecord_orderId` (`orderId`),
  CONSTRAINT `fk_ProductOrderRecord_orderId` FOREIGN KEY (`orderId`) REFERENCES `ProductOrder` (`id`)
);