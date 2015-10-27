CREATE TABLE `AppInfo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `platform` varchar(16) NOT NULL,
  `version` varchar(16) NOT NULL,
  `url` varchar(128) NOT NULL,
  `date` varchar(16) NOT NULL,
  PRIMARY KEY (`id`)
);

INSERT INTO AdminType(`platform`,`version`,`url`,`date`) VALUES('ios','1.0','http://','2015-10-10');
INSERT INTO AdminType(`platform`,`version`,`url`,`date`) VALUES('android','1.0','http://','2015-10-10');
