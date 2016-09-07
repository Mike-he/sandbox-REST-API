CREATE TABLE `AdvertisingAttachment` (
  `id` int(11) NOT NULL,
  `advertisingId` int(11) NOT NULL,
  `content` longtext COLLATE utf8_unicode_ci NOT NULL,
  `attachmentType` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `filename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `preview` longtext COLLATE utf8_unicode_ci,
  `size` int(11) NOT NULL,
  `height` int(11) NOT NULL,
  `width` int(11) NOT NULL,
   PRIMARY KEY (`id`),
  KEY `fk_AdvertisingAttachment_advertisingId_idx` (`advertisingId`),
  CONSTRAINT `fk_AdvertisingAttachment_advertisingId` FOREIGN KEY (`advertisingId`) REFERENCES `Advertising` (`id`) ON DELETE CASCADE
)
INSERT INTO `AdvertisingAttachment` (`advertisingId`,`content`,`attachmentType`,`filename`,`preview`,`size`,`height`,`width`) VALUES (1,"http://devimage.sandbox3.cn/advertising/1326x1080_coffee_ad.jpg","image/png","1326x1080_coffee_ad.jpg","http://devimage.sandbox3.cn/advertising/1326x1080_coffee_ad.jpg",3,1326,1080);
INSERT INTO `AdvertisingAttachment` (`advertisingId`,`content`,`attachmentType`,`filename`,`preview`,`size`,`height`,`width`) VALUES (1,"http://devimage.sandbox3.cn/advertising/1416x1080_coffee_ad.jpg","image/png","1416x1080_coffee_ad.jpg","http://devimage.sandbox3.cn/advertising/1416x1080_coffee_ad.jpg",3,1416,1080);
INSERT INTO `AdvertisingAttachment` (`advertisingId`,`content`,`attachmentType`,`filename`,`preview`,`size`,`height`,`width`) VALUES (1,"http://devimage.sandbox3.cn/advertising/1486x1080_coffee_ad.jpg","image/png","1486x1080_coffee_ad.jpg","http://devimage.sandbox3.cn/advertising/1486x1080_coffee_ad.jpg",3,1486,1080);
INSERT INTO `AdvertisingAttachment` (`advertisingId`,`content`,`attachmentType`,`filename`,`preview`,`size`,`height`,`width`) VALUES (1,"http://devimage.sandbox3.cn/advertising/1556x1080_coffee_ad.jpg","image/png","1556x1080_coffee_ad.jpg","http://devimage.sandbox3.cn/advertising/1556x1080_coffee_ad.jpg",3,1556,1080);
