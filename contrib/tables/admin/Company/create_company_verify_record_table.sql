CREATE TABLE `CompanyVerifyRecord` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `companyId` int(11) NOT NULL,
  `status` ENUM('pending','updated', 'rejected', 'accepted'),
  `companyInfo` longtext NOT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_CompanyVerifyRecord_companyId_idx` (`companyId`),
  CONSTRAINT `fk_CompanyVerifyRecord_companyId` FOREIGN KEY (`companyId`) REFERENCES `Company` (`id`) ON DELETE CASCADE
);