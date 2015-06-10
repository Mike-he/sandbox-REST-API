CREATE TABLE `CompanyApplication` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `appId` int(11) NOT NULL,
  `companyId` int(11) NOT NULL,
  `nameEN` varchar(32) DEFAULT NULL,
  `nameCN` varchar(32) DEFAULT NULL,
  `icon` varchar(16) DEFAULT NULL,
  `description` text,
  `url` varchar(1024) NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_companyapplication_companyId_idx` (`companyId`),
  CONSTRAINT `fk_companyapplication_companyId` FOREIGN KEY (`companyId`) REFERENCES `Company` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);
