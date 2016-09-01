CREATE TABLE `BannerTag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
);

INSERT INTO `BannerTag`(`name`) VALUES('activity');
INSERT INTO `BannerTag`(`name`) VALUES('news');
INSERT INTO `BannerTag`(`name`) VALUES('product');
INSERT INTO `BannerTag`(`name`) VALUES('advertisement');