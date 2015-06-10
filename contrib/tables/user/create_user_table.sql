CREATE TABLE `User` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `password` varchar(256) NOT NULL,
  `email` varchar(128) DEFAULT NULL,
  `countryCode` varchar(16) DEFAULT NULL,
  `phone` varchar(64) DEFAULT NULL,
  `xmppUsername` varchar(64) DEFAULT NULL,
  `activated` tinyint(4) NOT NULL DEFAULT '0',
  `creationDate` char(15) NOT NULL,
  `modificationDate` char(15) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `xmppUsername_UNIQUE` (`xmppUsername`),
  UNIQUE KEY `email_activated_UNIQUE` (`email`,`activated`),
  UNIQUE KEY `phoneNum_activated_UNIQUE` (`countryCode`,`phone`,`activated`)
);
