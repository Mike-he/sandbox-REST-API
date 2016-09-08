CREATE TABLE `BannerTag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
);

INSERT INTO `BannerTag`(`key`) VALUES('banner.tag.activity');
INSERT INTO `BannerTag`(`key`) VALUES('banner.tag.news');
INSERT INTO `BannerTag`(`key`) VALUES('banner.tag.product');
INSERT INTO `BannerTag`(`key`) VALUES('banner.tag.advertisement');