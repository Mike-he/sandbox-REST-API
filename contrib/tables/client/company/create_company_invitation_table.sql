CREATE TABLE `CompanyInvitation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `companyId` int(11) NOT NULL,
  `askUserId` int(11) NOT NULL,
  `recvUserId` int(11) NOT NULL,
  `status` enum('pending','accepted') NOT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_CompanyInvitation_companyId_idx` (`companyId`),
  KEY `fk_CompanyInvitation_askUserId_idx` (`askUserId`),
  KEY `fk_CompanyInvitation_recvUserId_idx` (`recvUserId`),
  CONSTRAINT `fk_CompanyInvitation_companyId` FOREIGN KEY (`companyId`) REFERENCES `Company` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_CompanyInvitation_askUserId` FOREIGN KEY (`askUserId`) REFERENCES `User` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_CompanyInvitation_recvUserId` FOREIGN KEY (`recvUserId`) REFERENCES `User` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);
