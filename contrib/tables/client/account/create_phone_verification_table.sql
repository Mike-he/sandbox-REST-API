CREATE TABLE `PhoneVerification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `countryCode` varchar(16) NOT NULL,
  `phone` varchar(64) NOT NULL,
  `token` varchar(64) NOT NULL,
  `code` varchar(16) NOT NULL,
  `creationDate` char(15) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_UNIQUE` (`token`)
);
