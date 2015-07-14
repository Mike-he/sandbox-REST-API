CREATE TABLE `CompanyPortfolio` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`companyId` int(11) NOT NULL,
`content` text NOT NULL,
`attachmentType` varchar(64) NOT NULL,
`fileName` varchar(64) NOT NULL,
`preview` varchar(64) DEFAULT NULL,
`size` int(11) DEFAULT NULL,
`creationDate` datetime NOT NULL,
`modificationDate` datetime NOT NULL,
PRIMARY KEY (`id`),
KEY `fk_CompanyPortfolio_companyId_idx` (`companyId`),
CONSTRAINT `fk_CompanyPortfolio_companyId` FOREIGN KEY (`companyId`) REFERENCES `Company` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);
