CREATE TABLE `CompanyMember` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` varchar(64) NOT NULL,
  `companyId` int(11) NOT NULL,
  `isDelete` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `userCompany_unique` (`userId`,`companyId`),
  KEY `fk_companymember_companyId_idx` (`companyId`),
  KEY `fk_company_member_userId_idx` (`userId`),
  CONSTRAINT `fk_companymember_companyId` FOREIGN KEY (`companyId`) REFERENCES `Company` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_company_member_userId` FOREIGN KEY (`userId`) REFERENCES `User` (`username`) ON DELETE NO ACTION ON UPDATE NO ACTION
)；