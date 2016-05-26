CREATE TABLE `BulletinType` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `deleted` tinyint(1) DEFAULT FALSE NOT NULL,
  `creationDate` datetime NOT NULL,
  PRIMARY KEY (`id`)
);