CREATE TABLE `CompanyIndustryMap` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `companyId` int(11) NOT NULL,
  `industryId` int(11) NOT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `companyId_industryId_UNIQUE` (`companyId`,`industryId`),
  KEY `fk_CompanyIndustryMap_companyId_idx` (`companyId`),
  KEY `fk_CompanyIndustryMap_industryId_idx` (`industryId`),
  CONSTRAINT `fk_CompanyIndustryMap_companyId` FOREIGN KEY (`companyId`) REFERENCES `Company` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_CompanyIndustryMap_industryId` FOREIGN KEY (`industryId`) REFERENCES `CompanyIndustry` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);

