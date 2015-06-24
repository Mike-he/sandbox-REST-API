CREATE TABLE `PhoneVerification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `phone` varchar(64) NOT NULL,
  `code` varchar(16) NOT NULL,
  `creationDate` datetime NOT NULL,
  PRIMARY KEY (`id`)
);
