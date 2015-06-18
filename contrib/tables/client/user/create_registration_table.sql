CREATE TABLE `UserRegistration` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phone` varchar(64) DEFAULT NULL,
  `email` varchar(128) DEFAULT NULL,
  `code` varchar(16) DEFAULT NULL,
  `creationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_UserRegistration__phone` (`phone`),
  UNIQUE KEY `uk_UserRegistration__email` (`email`)
);