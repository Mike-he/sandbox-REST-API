CREATE TABLE `Feature` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(16) NOT NULL,
  `ready` boolean DEFAULT FALSE NOT NULL,
  PRIMARY KEY (`id`)
);

INSERT INTO `features` (`id`,`name`,`type`,`url`,`ready`,`app`) VALUES (1,'food','web','https://testcoffee.sandbox3.cn/',1,'sandbox');
INSERT INTO `features` (`id`,`name`,`type`,`url`,`ready`,`app`) VALUES (2,'print','web','https://testprint.sandbox3.cn/',0,'sandbox');
INSERT INTO `features` (`id`,`name`,`type`,`url`,`ready`,`app`) VALUES (3,'coffee','web','https://testcoffee.sandbox3.cn/',1,'sandbox');
INSERT INTO `features` (`id`,`name`,`type`,`url`,`ready`,`app`) VALUES (4,'forward','web','https://testcafe.sandbox3.cn',1,'sandbox');
INSERT INTO `features` (`id`,`name`,`type`,`url`,`ready`,`app`) VALUES (5,'news','web','https://testm.sandbox3.cn/news',1,'sandbox');
INSERT INTO `features` (`id`,`name`,`type`,`url`,`ready`,`app`) VALUES (6,'event','web','https://testm.sandbox3.cn/event',1,'sandbox');
INSERT INTO `features` (`id`,`name`,`type`,`url`,`ready`,`app`) VALUES (7,'reservation','web','https://testmobile.sandbox3.cn/search',1,'sandbox');
INSERT INTO `features` (`id`,`name`,`type`,`url`,`ready`,`app`) VALUES (8,'invoice','web','https://testinvoice.sandbox3.cn/invoice',1,'sandbox');
INSERT INTO `features` (`id`,`name`,`type`,`url`,`ready`,`app`) VALUES (9,'about','web','https://testmobile.sandbox3.cn/about-xiehe',1,'xiehe');
INSERT INTO `features` (`id`,`name`,`type`,`url`,`ready`,`app`) VALUES (10,'reservation','web','https://testmobile.sandbox3.cn/search-xiehe',1,'xiehe');