CREATE TABLE `UserProfileVisitor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `visitorId` int(11) NOT NULL,
  `creationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_UserProfileVisitor_userId_idx` (`userId`),
  CONSTRAINT `fk_UserProfileVisitor_userId` FOREIGN KEY (`userId`) REFERENCES `User` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  KEY `fk_UserProfileVisitor_visitorId_idx` (`visitorId`),
  CONSTRAINT `fk_UserProfileVisitor_visitorId` FOREIGN KEY (`visitorId`) REFERENCES `User` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);
