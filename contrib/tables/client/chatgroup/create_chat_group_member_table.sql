CREATE TABLE `ChatGroupMember` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chatGroupId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `addBy` int(11) NOT NULL,
  `mute` tinyint(1) NOT NULL DEFAULT '0',
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `chatGroupId_userId_UNIQUE` (`chatGroupId`,`userId`),
  KEY `fk_ChatGroupMember_userId_idx` (`userId`),
  KEY `fk_ChatGroupMember_addBy_idx` (`addBy`),
  CONSTRAINT `fk_ChatGroupMember_addBy` FOREIGN KEY (`addBy`) REFERENCES `User` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_ChatGroupMember_chatGroupId` FOREIGN KEY (`chatGroupId`) REFERENCES `ChatGroup` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_ChatGroupMember_userId` FOREIGN KEY (`userId`) REFERENCES `User` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);
