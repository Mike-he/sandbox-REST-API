CREATE TABLE `ShopSpec` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `companyId` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  `description` text,
  `multiple` boolean DEFAULT FALSE,
  `invisible` boolean DEFAULT FALSE,
  `auto` boolean DEFAULT FALSE,
  `unit` varchar(15),
  PRIMARY KEY (`id`),
  UNIQUE KEY `companyId_name_UNIQUE` (`companyId`,`name`)
);