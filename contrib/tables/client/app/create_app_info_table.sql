CREATE TABLE `AppInfo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `platform` varchar(16) NOT NULL,
  `version` varchar(16) NOT NULL,
  `url` varchar(128) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
);
