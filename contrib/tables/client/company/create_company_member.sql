CREATE TABLE `CompanyMember` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `companyId` int(11) NOT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId_companyId_UNIQUE` (`userId`,`companyId`),
  KEY `fk_CompanyMember_companyId_idx` (`companyId`),
  KEY `fk_CompanyMember_userId_idx` (`userId`),
  CONSTRAINT `fk_CompanyMember_companyId` FOREIGN KEY (`companyId`) REFERENCES `Company` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_CompanyMember_userId` FOREIGN KEY (`userId`) REFERENCES `User` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);

