CREATE TABLE `parameter` (
  `id` int(11) NOT NULL,
  `key` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(128) COLLATE utf8_unicode_ci NOT NULL
  PRIMARY KEY (`id`)
)

INSERT INTO `parameter` (`key`, `value`) VALUES ('banner_top', '5');