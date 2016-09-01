CREATE TABLE `Banner` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `cover` text NOT NULL,
  `source` enum('event','news','url') NOT NULL,
  `sourceId` int(11),
  `sortTime` timestamp NOT NULL,,
  `tagId` int(11) NOT NULL,
  `subtitle` varchar(128) DEFAULT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  PRIMARY KEY (`id`)
);