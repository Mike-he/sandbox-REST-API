CREATE TABLE `Feed` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` text CHARACTER SET utf8mb4 NOT NULL,
  `ownerId` varchar(64) NOT NULL,
  `isDeleted` tinyint(1) DEFAULT FALSE NOT NULL,
  `creationDate` datetime NOT NULL,
  PRIMARY KEY (`id`)
);