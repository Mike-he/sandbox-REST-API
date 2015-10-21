CREATE TABLE `FoodFormOption` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `formId` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` numeric(15,2) NOT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_FoodFormOption_formId_idx` (`formId`),
  CONSTRAINT `fk_FoodFormOption_formId` FOREIGN KEY (`formId`) REFERENCES `FoodForm` (`id`) ON DELETE CASCADE
);