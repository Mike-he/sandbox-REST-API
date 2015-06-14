CREATE TABLE `Company` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) CHARACTER SET utf8mb4 NOT NULL,
  `address` varchar(1024) DEFAULT NULL,
  `website` varchar(256) DEFAULT NULL,
  `phone` varchar(32) DEFAULT NULL,
  `fax` varchar(32) DEFAULT NULL,
  `description` mediumtext CHARACTER SET utf8mb4,
  `creatorId` varchar(64) NOT NULL,
  `creationDate` char(15) NOT NULL,
  `modificationDate` char(15) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `creatorId_idx` (`creatorId`),
  CONSTRAINT `fk_company_creatorId` FOREIGN KEY (`creatorId`) REFERENCES `User` (`username`) ON DELETE NO ACTION ON UPDATE NO ACTION
);