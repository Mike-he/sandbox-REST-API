CREATE TABLE `UserPortfolio` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`userId` int(11) NOT NULL,
`content` text NOT NULL,
`attachmentType` varchar(64) NOT NULL,
`fileName` varchar(64) NOT NULL,
`preview` text,
`size` int(11) NOT NULL,
`creationDate` datetime NOT NULL,
`modificationDate` datetime NOT NULL,
PRIMARY KEY (`id`),
KEY `fk_UserPortfolio_userId_idx` (`userId`),
CONSTRAINT `fk_UserPortfolio_userId` FOREIGN KEY (`userId`) REFERENCES `User` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);
