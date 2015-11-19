CREATE TABLE `News` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(1024) NOT NULL,
  `description` varchar(1024) NOT NULL,
  `content` longtext NOT NULL,
  `isDeleted` tinyint(1) DEFAULT FALSE NOT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  PRIMARY KEY (`id`)
);