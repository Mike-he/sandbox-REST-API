CREATE TABLE `EmailVerification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `email` varchar(128) NOT NULL,
  `code` varchar(16) NOT NULL,
  `creationDate` char(15) NOT NULL,
  PRIMARY KEY (`id`)
);
