CREATE TABLE `WeChatShares` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `appId` varchar(128) NOT NULL,
  `secret` varchar(128) NOT NULL,
  `accessToken` varchar(1024) DEFAULT NULL,
  `jsapiTicket` varchar(1024) DEFAULT NULL,
  `expiresIn` varchar(1024) NOT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  PRIMARY KEY (`id`)
);

INSERT INTO `WeChatShares` (`appId`, `secret`, `expiresIn`, `creationDate`, `modificationDate`) VALUES ('wxd0bc8ff918c8e593', 'be4ed95443a324b27d19e313ad7dd2e0', '7200', '2016-08-18 00:00:00', '2016-08-18 00:00:00');