CREATE TABLE `Feature` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(16) NOT NULL,
  `ready` boolean DEFAULT FALSE NOT NULL,
  PRIMARY KEY (`id`)
);

INSERT INTO Feature(`name`,`ready`) VALUES('food', false);
INSERT INTO Feature(`name`,`ready`) VALUES('print', false);
INSERT INTO Feature(`name`,`ready`) VALUES('coffee', false);
INSERT INTO Feature(`name`,`type`,`url`,`ready`) VALUES('coffee', 'web', 'https://cafe.sandbox3.cn', false);
INSERT INTO `Feature` (`name`, `type`, `url`, `ready`) VALUES ('xiehe_about', 'web', 'https://testmobile.sandbox3.cn/about-xiehe', '1');
INSERT INTO `Feature` (`name`, `type`, `url`, `ready`) VALUES ('xiehe_main', 'web', 'https://testmobile.sandbox3.cn/search-xiehe', '1');
