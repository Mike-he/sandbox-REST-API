CREATE TABLE `Log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `platform` varchar(64) NOT NULL,
  `salesCompanyId` int(11),
  `adminUsername` varchar(64) NOT NULL,
  `logModule` varchar(64) NOT NULL,
  `logAction` varchar(64) NOT NULL,
  `logObjectKey` varchar(64) NOT NULL,
  `logObjectId` int(11) NOT NULL,
  `logObjectJson` text NOT NULL,
  `creationDate` datetime NOT NULL,
  PRIMARY KEY (`id`)
 );