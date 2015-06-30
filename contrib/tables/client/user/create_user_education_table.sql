CREATE TABLE `UserEducation` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`userId` int(11) NOT NULL,
`startDate` date DEFAULT NULL,
`endDate` date DEFAULT NULL,
`detail` text NOT NULL,
`creationDate` datetime NOT NULL,
`modificationDate` datetime NOT NULL,
PRIMARY KEY (`id`),
KEY `fk_userEducation_userId_idx` (`userId`)
);
