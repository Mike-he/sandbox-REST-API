CREATE TABLE `WeChatShare` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `keyName` VARCHAR(122) NOT NULL,
  `value` VARCHAR(1024) NOT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  PRIMARY KEY (`id`)
);