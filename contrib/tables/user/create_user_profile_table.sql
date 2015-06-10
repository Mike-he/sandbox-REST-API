CREATE TABLE `UserProfile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` varchar(64) NOT NULL,
  `companyId` int(11) DEFAULT NULL,
  `name` varchar(128) CHARACTER SET utf8mb4 NOT NULL,
  `email` varchar(128) DEFAULT NULL,
  `phone` varchar(32) DEFAULT NULL,
  `gender` enum('male','female','other') NOT NULL DEFAULT 'other',
  `whatsup` varchar(256) CHARACTER SET utf8mb4 DEFAULT NULL,
  `location` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userCompany_unique` (`userId`,`companyId`),
  KEY `vcard_userid_feky_idx` (`userId`),
  CONSTRAINT `vcard_userid_feky` FOREIGN KEY (`userId`) REFERENCES `User` (`username`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ;