CREATE TABLE `SalesCompany` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `applicantName` varchar(64) NOT NULL,
  `phone` varchar(64) NOT NULL,
  `email` varchar(255) NOT NULL,
  `description` longtext NOT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  `address` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
);