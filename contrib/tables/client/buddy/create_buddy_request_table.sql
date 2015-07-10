CREATE TABLE `BuddyRequest` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `askUserId` int(11) NOT NULL,
  `recvUserId` int(11) NOT NULL,
  `message` varchar(128) DEFAULT NULL,
  `status` enum('pending','accepted') NOT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_BuddyRequest_askUserId_idx` (`askUserId`),
  KEY `fk_BuddyRequest_recvUserId_idx` (`recvUserId`),
  CONSTRAINT `fk_BuddyRequest_askUserId` FOREIGN KEY (`askUserId`) REFERENCES `User` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_BuddyRequest_recvUserId` FOREIGN KEY (`recvUserId`) REFERENCES `User` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);
