CREATE TABLE `FoodAttachment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `foodId` int(11) NOT NULL,
  `content` longtext NOT NULL,
  `attachmentType` varchar(64) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `preview` longtext NOT NULL,
  `size` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_FoodAttachment_foodId_idx` (`foodId`),
  CONSTRAINT `fk_FoodAttachment_foodId` FOREIGN KEY (`foodId`) REFERENCES `Food` (`id`) ON DELETE CASCADE
);