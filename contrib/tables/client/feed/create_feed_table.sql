CREATE TABLE `Feed` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` text CHARACTER SET utf8mb4 NOT NULL,
  `parentID` int(11) NOT NULL,
  `parentType` enum('company') NOT NULL,
  `ownerID` varchar(64) NOT NULL,
  `creationDate` char(15) NOT NULL,
  PRIMARY KEY (`id`)
);