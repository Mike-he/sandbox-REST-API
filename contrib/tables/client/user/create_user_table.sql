CREATE TABLE `User` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `xmppUsername` varchar(64) DEFAULT NULL,
  `password` varchar(256) NOT NULL,
  `email` varchar(128) DEFAULT NULL,
  `phone` varchar(64) DEFAULT NULL,
  `banned` tinyint(4) NOT NULL,
  `authorized` tinyint(4) NOT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `xmppUsername_UNIQUE` (`xmppUsername`),
  UNIQUE KEY `email_UNIQUE` (`email`),
  UNIQUE KEY `phone_UNIQUE` (`phone`)
);
