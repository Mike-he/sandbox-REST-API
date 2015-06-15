CREATE TABLE `AdminType` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(16) NOT NULL,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_UNIQUE` (`key`)
);
