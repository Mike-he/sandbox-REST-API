CREATE TABLE `ForgetPassword` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `code` varchar(16) NOT NULL,
  `status` enum('submit','verify') NOT NULL,
  `type` enum('email','phone') NOT NULL,
  `creationDate` char(15) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_UNIQUE` (`token`)
);
