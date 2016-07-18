CREATE TABLE `SalesCompanyUserCard` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(64) NOT NULL,
  `companyId` int(11) DEFAULT NULL,
  `cardUrl` longtext,
  `cardBackgroundUrl` longtext,
  `cardNumberColor` varchar(64) DEFAULT NULL,
  `lostCardBackgroundUrl` longtext,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  PRIMARY KEY (`id`)
);

INSERT INTO `sandbox_rest_api_db`.`SalesCompanyUserCard` (`type`, `companyId`, `cardUrl`, `cardBackgroundUrl`, `cardNumberColor`, `creationDate`, `modificationDate`, `lostCardBackgroundUrl`) VALUES ('sales', '2', 'https://image.sandbox3.cn/user_card/sandbox3_user_card.png', 'https://image.sandbox3.cn/user_card/sandbox3_user_card_bg.png', '#e8d47c', '2016-07-06 15:27:00', '2016-07-06 15:27:00', 'https://image.sandbox3.cn/user_card/sandbox3_user_card_lost_bg.png');
