CREATE TABLE `Room` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255),
  `description` text,
  `city` varchar(255) NOT NULL,
  `building` varchar(255) NOT NULL,
  `floor` int(11) NOT NULL,
  `number` varchar(64) NOT NULL,
  `allowedPeople` int(11) NOT NULL,
  `area` int(11) NOT NULL,
  `officeSupplies` int(11),
  `type` enum('office','meeting','flexible','fixed') NOT NULL,
  `creationDate` DATETIME NOT NULL,
  `modificationDate` DATETIME NOT NULL,
  PRIMARY KEY (`id`)
);
