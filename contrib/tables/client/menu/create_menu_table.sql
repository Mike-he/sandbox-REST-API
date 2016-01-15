CREATE TABLE `Menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `component` varchar(16) NOT NULL,
  `key` varchar(16) NOT NULL,
  `type` varchar(16) NOT NULL,
  `url` varchar(512) NOT NULL,
  `ready` tinyint(1) NOT NULL,
  `platform` varchar(16) NOT NULL,
  `version` varchar(16) NOT NULL,
  `position` varchar(16) NOT NULL,
  `section` int(11) NOT NULL,
  `part` int(11) NOT NULL,
  `number` int(11) NOT NULL,
  PRIMARY KEY (`id`)
);

INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'community', 'app', '', '1', 'iphone', '2.1', 'left', '1', '1', '1');
INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'community', 'app', '', '1', 'android', '2.1', 'left', '1', '1', '1');

INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'order', 'web', 'http://devmobile.sandbox3.cn/search', '1', 'iphone', '2.1', 'left', '1', '2', '1');
INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'order', 'web', 'http://devmobile.sandbox3.cn/search', '1', 'android', '2.1', 'left', '1', '2', '1');

INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'feed', 'app', '', '1', 'iphone', '2.1', 'left', '1', '3', '1');
INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'feed', 'app', '', '1', 'android', '2.1', 'left', '1', '3', '1');

INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'message', 'app', '', '1', 'iphone', '2.1', 'left', '1', '3', '2');
INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'message', 'app', '', '1', 'android', '2.1', 'left', '1', '3', '2');

INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'contact', 'app', '', '1', 'iphone', '2.1', 'left', '1', '4', '1');
INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'contact', 'app', '', '1', 'android', '2.1', 'left', '1', '4', '1');

INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'member', 'app', '', '1', 'iphone', '2.1', 'left', '1', '4', '2');
INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'member', 'app', '', '1', 'android', '2.1', 'left', '1', '4', '2');

INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'company', 'app', '', '1', 'iphone', '2.1', 'left', '1', '4', '3');
INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'company', 'app', '', '1', 'android', '2.1', 'left', '1', '4', '3');

INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'my_company', 'app', '', '1', 'iphone', '2.1', 'left', '1', '4', '4');
INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'my_company', 'app', '', '1', 'android', '2.1', 'left', '1', '4', '4');

INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'event', 'web', 'http://devmobile.sandbox3.cn/event', '1', 'iphone', '2.1', 'left', '1', '4', '5');
INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'event', 'web', 'http://devmobile.sandbox3.cn/event', '1', 'android', '2.1', 'left', '1', '4', '5');

INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'scan', 'app', '', '1', 'iphone', '2.1', 'left', '2', '1', '1');
INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'scan', 'app', '', '1', 'android', '2.1', 'left', '2', '1', '1');

INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'location', 'app', '', '1', 'iphone', '2.1', 'left', '2', '1', '2');
INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'location', 'app', '', '1', 'android', '2.1', 'left', '2', '1', '2');

INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'food', 'web', 'http://devfood.sandbox3.cn', '0', 'iphone', '2.1', 'left', '2', '1', '3');
INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'food', 'web', 'http://devfood.sandbox3.cn', '0', 'android', '2.1', 'left', '2', '1', '3');

INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'print', 'web', 'http://devprint.sandbox3.cn', '0', 'iphone', '2.1', 'left', '2', '1', '4');
INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'print', 'web', 'http://devprint.sandbox3.cn', '0', 'android', '2.1', 'left', '2', '1', '4');

INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'topup', 'web', 'http://devmobile.sandbox3.cn/recharge', '1', 'iphone', '2.1', 'right', '1', '1', '1');
INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'topup', 'web', 'http://devmobile.sandbox3.cn/recharge', '1', 'android', '2.1', 'right', '1', '1', '1');

INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'my_order', 'app', '', '1', 'iphone', '2.1', 'right', '1', '2', '1');
INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'my_order', 'app', '', '1', 'android', '2.1', 'right', '1', '2', '1');

INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'my_room', 'app', '', '1', 'iphone', '2.1', 'right', '1', '2', '2');
INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'my_room', 'app', '', '1', 'android', '2.1', 'right', '1', '2', '2');

INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'announcement', 'app', '', '1', 'iphone', '2.1', 'right', '1', '2', '3');
INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'announcement', 'app', '', '1', 'android', '2.1', 'right', '1', '2', '3');

INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'my_invoice', 'app', '', '1', 'iphone', '2.1', 'right', '1', '2', '4');
INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'my_invoice', 'app', '', '1', 'android', '2.1', 'right', '1', '2', '4');

INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'member_card', 'app', '', '1', 'iphone', '2.1', 'right', '1', '2', '5');
INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'member_card', 'app', '', '1', 'android', '2.1', 'right', '1', '2', '5');

INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'reset_password', 'app', '', '1', 'iphone', '2.1', 'right', '1', '3', '1');
INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'reset_password', 'app', '', '1', 'android', '2.1', 'right', '1', '3', '1');

INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'email', 'app', '', '1', 'iphone', '2.1', 'right', '1', '3', '2');
INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'email', 'app', '', '1', 'android', '2.1', 'right', '1', '3', '2');

INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'phone', 'app', '', '1', 'iphone', '2.1', 'right', '1', '3', '3');
INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'phone', 'app', '', '1', 'android', '2.1', 'right', '1', '3', '3');

INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'about_us', 'web', 'http://devmobile.sandbox3.cn/about', '1', 'iphone', '2.1', 'right', '1', '4', '1');
INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'about_us', 'web', 'http://devmobile.sandbox3.cn/about', '1', 'android', '2.1', 'right', '1', '4', '1');

INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'setting', 'app', '', '1', 'iphone', '2.1', 'right', '1', '4', '2');
INSERT INTO `Menu` (`component`, `key`, `type`, `url`, `ready`, `platform`, `version`, `position`, `section`, `part`, `number`) VALUES ('client', 'setting', 'app', '', '1', 'android', '2.1', 'right', '1', '4', '2');

