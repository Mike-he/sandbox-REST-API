CREATE TABLE `FoodForm` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `foodId` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `type` enum('cupSize','single','multiple') NOT NULL,
  `required` boolean DEFAULT TRUE NOT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_FoodForm_foodId_idx` (`foodId`),
  CONSTRAINT `fk_FoodForm_foodId` FOREIGN KEY (`foodId`) REFERENCES `Food` (`id`) ON DELETE CASCADE
);