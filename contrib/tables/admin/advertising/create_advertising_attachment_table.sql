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
