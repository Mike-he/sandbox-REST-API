CREATE TABLE `Advertising` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` longtext COLLATE utf8_unicode_ci NOT NULL,
  `source` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `sourceId` int(11) DEFAULT NULL,
  `visible` tinyint(1) NOT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  `isSaved` tinyint(1) NOT NULL,
  `isDefault` tinyint(1) NOT NULL
  PRIMARY KEY (`id`)
)

INSERT INTO `Advertising` (`url`,`source`,`sourceId`,`visible`,`isSaved`,`isDefault`,`creationDate`,`modificationDate`) VALUES ("https://m.sandbox3.cn/event?ptype=detail&id=50","event",50,1,0,1,"2016-08-19 15:19:34","2016-08-19 15:19:34");
