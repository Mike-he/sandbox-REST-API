CREATE TABLE `PriceRuleBinding` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `productId` int(11) NOT NULL,
  `priceRuleId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_PriceRuleBinding_productId_idx` (`productId`),
  CONSTRAINT `fk_PriceRuleBinding_productId` FOREIGN KEY (`productId`) REFERENCES `Product` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);