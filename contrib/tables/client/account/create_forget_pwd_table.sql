CREATE TABLE `ForgetPassword` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `email` varchar(128) DEFAULT NULL,
  `phone` varchar(64) DEFAULT NULL,
  `token` varchar(64) DEFAULT NULL,
  `code` varchar(16) NOT NULL,
  `status` enum('submit','verify') NOT NULL,
  `type` enum('email','phone') NOT NULL,
  `creationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_UNIQUE` (`token`)
);
